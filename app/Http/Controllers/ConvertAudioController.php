<?php

namespace App\Http\Controllers;

use App\ConvertAudio;
use Illuminate\Http\Request;

class ConvertAudioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\ConvertAudio $convertAudio
     * @return \Illuminate\Http\Response
     */
    public function show(ConvertAudio $convertAudio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\ConvertAudio $convertAudio
     * @return \Illuminate\Http\Response
     */
    public function edit(ConvertAudio $convertAudio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\ConvertAudio $convertAudio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConvertAudio $convertAudio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ConvertAudio $convertAudio
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConvertAudio $convertAudio)
    {
        //
    }

    public function convert(Request $request)
    {
        $foldertype = request('foldertype');
        $filename = request('filename');

        $fullfiles = "";
        $files = scandir($foldertype);
        foreach ($files as $file) {
            if (strpos($file, $filename) !== 'false') {
                if (strpos($foldertype, 'audio') !== 'false') {
                    if (strpos($file, '.tmp') !== 'false') {

                    } else {
                        $realpath = "http://41.189.178.19/schooldirect/audio/" . $file;
                        $fullfiles = $realpath . ";" . $fullfiles;
                    }
                } elseif (strpos($foldertype, 'video') !== 'false') {
                    if (strpos($file, '.tmp') !== 'false') {

                    } else {
                        $realpath = "http://41.189.178.19/schooldirect/video/" . $file;
                        $fullfiles = $realpath . ";" . $fullfiles;
                    }
                }
            }
        }
    }
}
