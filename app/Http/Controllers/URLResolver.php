<?php

namespace App\Http\Controllers;

use App\Models\Shortener;

class URLResolver extends Controller
{
    public function resolve($short_url)
    {
        $full_url = Shortener::select('long_url')->where('short_url', $short_url)->firstOrFail()->long_url;

        return redirect($full_url);
    }
}
