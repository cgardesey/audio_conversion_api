<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\Process\Process;

class ConvertAudio extends Model
{
    //
    public function convert(Request $request)
    {
        $audio = Audio::where('sessionid', '=', request('sessionid'))->first();
        $time_created = Carbon::createFromFormat('Y-m-d H:i:s', $audio->created_at);
        $now = Carbon::now();
        if (($audio->audiourl == null || $audio->audiourl == "") && $audio->gsmcalllink != null && $audio->gsmcalllink != "" && $now->diffInRealMinutes($time_created) > 180) {
            $audioname = basename($audio->created_at, ".wav");
            $process = new Process(['ffmpeg', '-protocol_whitelist', 'file', 'http', 'https', 'tcp', '-i', $audio->gsmcalllink, '-vn', '-ar', '44100', '-ac', '2', '-b:a', '192k', '/var/www/html/convertedaudiofiles/' . $audioname]);
            $process->run();
        }

        return Response::json(array(
            'success' => true
        ));
    }
}
