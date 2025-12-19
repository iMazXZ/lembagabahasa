<?php

namespace App\Http\Controllers;

use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome', [
            'news'      => Post::published()->type('news')
                ->latest('published_at')->limit(6)
                ->get(['title','slug','type','excerpt','cover_path','published_at']),
            // Jadwal diurutkan berdasarkan published_at (terbaru dibuat)
            'schedules' => Post::published()->type('schedule')
                ->latest('published_at')->limit(6)
                ->get(['title','slug','type','excerpt','cover_path','published_at','event_date','event_time','event_location']),
            'scores'    => Post::published()->type('scores')
                ->latest('published_at')->limit(6)
                ->get(['title','slug','type','excerpt','cover_path','published_at']),
        ]);
    }
}
