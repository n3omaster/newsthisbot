<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class MediaBuilderController extends Controller
{
    // CNN:
    // Text: 61 to 500
    // Image: 500 to 900
    // Ads: 900 to 975

    // Imag container by defult
    private $metrics = [1200, 630];
    private $newspapers = ['cnn', 'fox', 'abc', 'bbc', 'nbc', 'nyt', 'granma', 'cd'];

    /**
     * Make a new Cover with predefined models
     */
    public function make_news_cover($newspaper = 'cnn', $text = 'This is a text to the image wirbwr pguhbw rghpuhwe rgo;uwehr gwr rgiub rgueir This is a text to the image wirbwr pguhbw rghpuhwe rgo;uwehr gwr rgiub rgueir', $image = 'images/FVUJNE_WQAcghIK.jpg')
    {
        $img = Image::make($image);
        $img->fit($this->metrics[0], $this->metrics[1], function ($constraint) {
            $constraint->upsize();
        });

        $picture = Image::make('newspapers/' . $newspaper . '.png');
        $img->insert($picture, 'top-left', 0, 0);

        // Add text to the image from pixel 61 to 500
        $text_image = $this->image_container($text);
        $img->insert($text_image, 'top-left', 10, 500);

        return $img->response('png');
    }


    /**
     * Create an image containing text
     */
    public function image_container($text = 'The quick brown fox jumps over the lazy dog. The quick brown fox jumps over the lazy dog?')
    {
        $width       = 1055;
        $height      = 80;
        $center_x    = $width / 2;
        $center_y    = $height / 2;
        $max_len     = 80;
        $font_size   = 30;
        $font_height = 20;

        $lines = explode("\n", wordwrap($text, $max_len));
        $y     = $center_y - ((count($lines) - 1) * $font_height);
        $img   = Image::canvas($width, $height);

        foreach ($lines as $line) {
            $img->text($line, $center_x, $y, function ($font) use ($font_size) {
                $font->file('fonts/ptsans.ttf');
                $font->size($font_size);
                $font->color('#202020');
                $font->align('center');
                $font->valign('center');
            });

            $y += $font_height * 2;
        }

        return $img;
    }
}
