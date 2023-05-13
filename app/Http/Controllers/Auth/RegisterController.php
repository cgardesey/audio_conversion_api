<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enrolment;
use App\Payment;
use App\Period;
use App\Timetable;
use App\User;
use App\Instructor;
use App\Mail\PasswordResetLinkSent;
use App\Mail\VerificationEmailSent;
use App\PasswordReset;
use App\Student;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//        $this->middleware('guest');
//    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
//            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function store()
    {
        $user = User::where('email', request('email'))->first();
        if (!$user) {
            $userid = Str::uuid();
            $created_user = User::forceCreate([
                'userid' => $userid,
                'email' => request('email'),
                'password' => Hash::make(request('password')),
                'role' => request('role'),
                'api_token' => Str::uuid(),
                'confirmation_token' => Str::random(40)
            ]);
            if ($created_user->role == 'student') {
                Student::forceCreate([
                    'infoid' => $userid
                ]);
            } else if ($created_user->role == 'instructor') {
                Instructor::forceCreate([
                    'infoid' => $userid
                ]);
            }
            Mail::to($created_user->email)->send(
                new VerificationEmailSent($created_user)
            );

            return '1';
        } else {
            return '0';
        }
    }

    protected function resendVerificationLink()
    {
        $user = User::where('email', request('email'))->first();
        Mail::to($user->email)->send(
            new VerificationEmailSent($user)
        );
        return '0';
    }

    protected function confirmEmail($email, $token)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            flash('Email not found.')->error();
            return Redirect::to("register/confirm/$token");
        }

        if ($user->confirmation_token == null && $user->email_verified == 1) {
            flash('Your email has been verified already.')->info();
        } else {
            $user->confirmation_token = null;
            $user->email_verified = 1;

            $user->save();
            flash('Email successfully verified. Your can now proceed to login!')->info();
        }
        return Redirect::to("register/confirm/$token");
    }

    public function login(){
        $user = User::where('email', request('email'))->first();

        if(!$user){
            return response()->json(array(
                'status' => 0 // user not found
            ));
        }
        else {
            if (request('email')) {
                return $this->sendAll($user);
            }
            elseif (!$user->email_verified) {
                return response()->json(array(
                    'status' => -1 // email not verified
                ));
            }
            elseif (Hash::check(request('password'), $user->password)){
                return $this->sendAll($user);
            }
            else {
                return response()->json(array(
                    'status' => 2 // Incorrect password
                ));
            }
        }
    }

    private function sendAll(Request $request, $user): \Illuminate\Http\JsonResponse
    {
        $instructorcourses = $user->info->instructorCourses;
        // fetch assignments
        /*$instructorcourses = $user->info->instructorCourses;
        $assignments = [];
        $instructor_course_assignments_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_assignments_arrays[] = $instructorcourse->assignments;
        }
        foreach ($instructor_course_assignments_arrays as $instructor_course_assignments_array) {
            foreach ($instructor_course_assignments_array as $instructor_course_assignment) {
                $assignments[] = $instructor_course_assignment;
            }
        }*/

        // fetch attendances
        $attendances = $user->info->attendances;


        // fetch audios
        /*$audios = [];
        $instructor_course_audios_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_audios_arrays[] = $instructorcourse->audios;
        }
        foreach ($instructor_course_audios_arrays as $instructor_course_audios_array) {
            foreach ($instructor_course_audios_array as $instructor_course_audio) {
                $audios[] = $instructor_course_audio;
            }
        }*/


        // fetch chats
//        $chats = [];
//        $instructor_course_chat_arrays = [];
//        foreach ($instructorcourses as $instructorcourse) {
//            $instructor_course_chat_arrays[] = $instructorcourse->chats;
//        }
//        foreach ($instructor_course_chat_arrays as $instructor_course_chat_array) {
//            foreach ($instructor_course_chat_array as $instructor_course_chat) {
//                $chats[] = $instructor_course_chat;
//            }
//        }


        // fetch courses
        $instructor_courses = $user->info->instructorCourses;
        $courses = [];
        foreach ($instructor_courses as $instructor_course) {
            $courses[] = $instructor_course->course;
        }


        // fetch enrolments
        /*$instructorCourses = $user->info->instructorCourses;
        $enrolments = [];
        $instructor_course_student_arrays = [];
        foreach ($instructorCourses as $instructorCourse) {
            $instructor_course_student_arrays[] = $instructorCourse->students;
        }
        foreach ($instructor_course_student_arrays as $instructor_course_student_array) {
            foreach ($instructor_course_student_array as $instructor_course_student) {
                $enrolments[] = $instructor_course_student->enrolment;
            }
        }
        $enrolments = collect($enrolments)->unique('enrolmentid')->values()->all();*/
        $enrolments = Enrolment::where('studentid', $user->userid)
            ->where('enrolled', 1)
            ->get();

        // fetch instructors
        $instructor_courses = $user->info->instructorCourses;
        $instructors = [];
        foreach ($instructor_courses as $instructor_course) {
            $instructors[] = $instructor_course->instructor;
        }


        // fetch instructor-courses


        // fetch payments
        Payment::whereNull('paymentref')->delete();
        $payments = Payment::where('payerid', '=', $user->userid)->get();


        //fetch institutions
        $instructorcourses = $user->info->instructorCourses;
        $institutions = [];
        foreach ($instructorcourses as $instructorcourse) {
            $institution = $instructorcourse->institution;
            if ($institution) {
                $institutions[] = $institution;
            }
        }

        // fetch students
        $instructorCourses = $user->info->instructorCourses;
        $students = [];
        $students[] = $user->info;
        $instructor_course_student_arrays = [];
        foreach ($instructorCourses as $instructorCourse) {
            $instructor_course_student_arrays[] = $instructorCourse->students;
        }
        foreach ($instructor_course_student_arrays as $instructor_course_student_array) {
            foreach ($instructor_course_student_array as $instructor_course_student) {
                $students[] = $instructor_course_student;
            }
        }
        $students = collect($students)->unique('infoid')->values()->all();


        // fetch submitted-assignments
        $submitted_assignments = $user->info->submittedAssignments;

        // fetch submitted-quizzes
        $submitted_quizzes = $user->info->submittedQuizzes;

        // fetch users
        $instructorCourses = $user->info->instructorCourses;
        $users = [];
        $instructor_course_student_arrays = [];
        foreach ($instructorCourses as $instructorCourse) {
            $users[] = User::find($instructorCourse->instructor->infoid);
            $instructor_course_student_arrays[] = $instructorCourse->students;
        }
        foreach ($instructor_course_student_arrays as $instructor_course_student_array) {
            foreach ($instructor_course_student_array as $instructor_course_student) {
                $users[] = $instructor_course_student->user;
            }
        }
        $users = collect($users)->unique('userid')->values()->all();


        // fetch periods
        $periods = Period::all();


        // fetch timetables
        $instructor_courses = $user->info->instructorCourses;
        $timetables = [];
        $timetables_arrays = [];
        foreach ($instructor_courses as $instructor_course) {
            $timetables_arrays[] = Timetable::where('instructorcourseid', $instructor_course->instructorcourseid)->get();
        }
        foreach ($timetables_arrays as $timetables_array) {
            foreach ($timetables_array as $timetable) {
                $timetables[] = $timetable;
            }
        }


        // fetch instructor-course-ratings
        $instructor_courses = $user->info->instructorCourses;
        $instructor_course_ratings = [];
        $instructor_course_rating_arrays = [];
        foreach ($instructor_courses as $instructor_course) {
            $instructor_course_rating_arrays[] = $instructor_course->ratings;
        }
        foreach ($instructor_course_rating_arrays as $instructor_course_rating_array) {
            foreach ($instructor_course_rating_array as $instructor_course_rating) {
                $instructor_course_ratings[] = $instructor_course_rating;
            }
        }

        // fetch quizzes
        /*$instructorcourses = $user->info->instructorCourses;
        $quizzes = [];
        $instructor_course_quizzes_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_quizzes_arrays[] = $instructorcourse->quizzes;
        }
        foreach ($instructor_course_quizzes_arrays as $instructor_course_quizzes_array) {
            foreach ($instructor_course_quizzes_array as $instructor_course_quiz) {
                $quizzes[] = $instructor_course_quiz;
            }
        }*/

        // fetch dialcodes
        $dialcodes = DB::table('dialcode')->get();

        // fetch videos
        $instructorcourses = $user->info->instructorCourses;
        $recorded_videos = [];
        $instructor_course_recorded_videos_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_recorded_videos_arrays[] = $instructorcourse->recordedVideos;
        }
        foreach ($instructor_course_recorded_videos_arrays as $instructor_course_recorded_videos_array) {
            foreach ($instructor_course_recorded_videos_array as $instructor_course_recorded_video) {
                $recorded_videos[] = $instructor_course_recorded_video;
            }
        }

        /*// fetch recorded-video-streams
        $instructorcourses = $user->info->instructorCourses;
        $recorded_video_streams = [];
        $instructor_course_recorded_video_streams_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_recorded_video_streams_arrays[] = $instructorcourse->recordedVideoStreams;
        }
        foreach ($instructor_course_recorded_video_streams_arrays as $instructor_course_recorded_video_streams_array) {
            foreach ($instructor_course_recorded_video_streams_array as $instructor_course_recorded_video_stream) {
                $recorded_video_streams[] = $instructor_course_recorded_video_stream;
            }
        }*/


        // fetch recorded-audio-streams
        /*$instructorcourses = $user->info->instructorCourses;
        $recorded_audio_streams = [];
        $instructor_course_recorded_audio_streams_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_recorded_audio_streams_arrays[] = $instructorcourse->recordedAudioStreams;
        }
        foreach ($instructor_course_recorded_audio_streams_arrays as $instructor_course_recorded_audio_streams_array) {
            foreach ($instructor_course_recorded_audio_streams_array as $instructor_course_recorded_audio_stream) {
                $recorded_audio_streams[] = $instructor_course_recorded_audio_stream;
            }
        }*/

        //fetch app-user-fees
        $app_user_fees = DB::table('app_user_fees')->get();

        // fetch drawing-coordinates
        /*$instructorcourses = $user->info->instructorCourses;
        $drawing_coordinates = [];
        $instructor_course_drawing_coordinates_arrays = [];
        foreach ($instructorcourses as $instructorcourse) {
            $instructor_course_drawing_coordinates_arrays[] = $instructorcourse->drawingCoordinates;
        }
        foreach ($instructor_course_drawing_coordinates_arrays as $instructor_course_drawing_coordinates_array) {
            foreach ($instructor_course_drawing_coordinates_array as $instructor_course_drawing_coordinate) {
                $drawing_coordinates[] = $instructor_course_drawing_coordinate;
            }
        }*/

        $user->update(
            [
                'guid' => request('guid'),
                'apphash' => request('apphash'),
                'osversion' => request('osversion'),
                'sdkversion' => request('sdkversion'),
                'device' => request('device'),
                'devicemodel' => request('devicemodel'),
                'deviceproduct' => request('deviceproduct'),
                'manufacturer' => request('manufacturer'),
                'androidid' => request('androidid'),
                'versionrelease' => request('versionrelease'),
                'deviceheight' => request('deviceheight'),
                'devicewidth' => request('devicewidth')
            ]
        );
        return Response::json(array(
            'successful' => true, // Successful
            'userid' => $user->userid,
            'api_token' => $user->api_token,
            'connected' => 1,
//            'assignments' => $assignments,
//            'attendances' => $attendances,
//            'audios' => $audios,
//            'chats' => $chats,
            'courses' => $courses,
            'enrolments' => $enrolments,
            'instructors' => $instructors,
            'instructor_courses' => $instructor_courses,
            'payments' => $payments,
            'institutions' => $institutions,
            'students' => $students,
//            'submitted_assignments' => $submitted_assignments,
            'users' => $users,
            'periods' => $periods,
            'timetables' => $timetables,
            'instructor_course_ratings' => $instructor_course_ratings,
//            'quizzes' => $quizzes,
//            'submitted_quizzes' => $submitted_quizzes,
            'dialcodes' => $dialcodes,
//            'recorded_videos' => $recorded_videos,
//            'recorded_video_streams' => $recorded_video_streams,
//            'recorded_audio_streams' => $recorded_audio_streams,
            'app_user_fees' => $app_user_fees,
//            'drawing_coordinates' => $drawing_coordinates
        ));
    }
}
