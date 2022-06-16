<?php

namespace App\Http\Controllers;

use App\Models\Tweet as TweetModel;
use Intervention\Image\Facades\Image;
use Coderjerk\BirdElephant\Compose\Reply;
use Coderjerk\BirdElephant\Compose\Tweet;

class CommandController extends Controller
{
    private $twitter;
    private $commands = ['idem', 'cnn'];
    private $responses = [
        'Tócate otra cosita, papi.',
        '¿Cual es la gracia? ¿Cual es la gracia?',
        'Papi no estoy pa ti hoy',
        'Te me calmas, que son una pila, te me calmas.',
        'Bah, a esta hora y con este recado'
    ];

    // public constructor for commands options
    public function build($twitter, $tweet)
    {
        // Twitter Intance
        $this->twitter = $twitter;

        // get referenced Tweet condition, dont reply to direct tweets, always as comment of another tweet
        if (isset($tweet->referenced_tweets[0]->type) && $tweet->referenced_tweets[0]->type == 'replied_to' && isset($tweet->referenced_tweets[0]->id)) {

            // Explode the tweet mention data
            $exploded = explode(" ", $tweet->text);
            $handle_position = array_search("@newsthisbot", $exploded);                                         // You can even determine the handle psition and check if its on 0
            $command = isset($exploded[$handle_position + 1]) ? $exploded[$handle_position + 1] : "";

            // check if the command is valid
            if (in_array($command, $this->commands)) {

                echo "<h2>Command</h2>";
                echo $command;

                // get referenced Tweet
                $referenced_tweet = $this->twitter->tweets()->get($tweet->referenced_tweets[0]->id, [
                    'expansions' => 'attachments.media_keys,author_id,entities.mentions.username,in_reply_to_user_id,referenced_tweets.id',
                    'media.fields' => 'preview_image_url,url',
                ]);
                dump($referenced_tweet);

                // Reply to the referenced Tweet $referenced_tweet->data->id
                echo "<h2>Tweet ID</h2>";
                echo $tweet->id;
                echo "<h2>Referenced Tweet ID</h2>";
                echo $referenced_tweet->data->id;

                // Look for the tweet_id on DB and see if its replied or not
                $replied = TweetModel::where('tweet_id', $tweet->id)->first();
                if (!$replied) {

                    // Process Command name as function
                    return $this->$command($tweet, $referenced_tweet);
                }
            }
        }

        return false;
    }

    /**
     * Same text Command response
     */
    public function same($tweet, $referenced_tweet)
    {
        echo "<h1>SAME</h1>";
        echo "<p>" . $referenced_tweet->data->text . "</p>";

        // Now reply, save $tweet->id into DB for no more responses to it
        $reply = (new Reply)->inReplyToTweetId($tweet->id);
        $new_tweet = (new Tweet)->text($referenced_tweet->data->text)->reply($reply);

        return $this->twitter->tweets()->tweet($new_tweet);
    }

    /**
     * CNN news Command response
     */
    public function cnn($tweet, $referenced_tweet)
    {
        echo "<h1>CNN</h1>";
        echo "<p>" . $referenced_tweet->data->text . "</p>";

        // Now reply, save $tweet->id into DB for no more responses to it
        $reply = (new Reply)->inReplyToTweetId($tweet->id);
        $new_tweet = (new Tweet)->text($this->responses[rand(0, count($this->responses) - 1)])->reply($reply);

        dump($new_tweet);

        // try to get the image object and download it
        if (isset($referenced_tweet->includes->media[0]->type) && $referenced_tweet->includes->media[0]->type == 'photo' && isset($referenced_tweet->includes->media[0]->url)) {

            $image = $referenced_tweet->includes->media[0]->url;

            // Now save this image into filesystem
            $path = $image;
            $referenced_tweet_filename = basename($path);

            Image::make($path)->save(public_path('images/' . $referenced_tweet_filename));
        }

        // If no $referenced_tweet_filename then use a custom image
        if (isset($referenced_tweet_filename) && file_exists('images/' . $referenced_tweet_filename)) {
            $upload = 'images/' . $referenced_tweet_filename;
            echo "<h2>" . $upload . "</h2>";
        } else {
            $upload = 'images/default.jpeg';
        }

        // first, use the tweeets()->upload method to upload your image file
        $image = $this->twitter->tweets()->upload($upload);

        // pass the returned media id to a media object as an array
        $media = (new \Coderjerk\BirdElephant\Compose\Media)->mediaIds(
            [
                $image->media_id_string
            ]
        );

        // Retunr Twitter object
        $new_tweet = $new_tweet->media($media);

        dump($new_tweet);

        return $new_tweet;
    }
}
