<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweet as TweetModel;
use Intervention\Image\Facades\Image;
use Coderjerk\BirdElephant\BirdElephant;

use Coderjerk\BirdElephant\Compose\Reply;
use Coderjerk\BirdElephant\Compose\Tweet;

class WelcomeController extends Controller
{
    private $twitter;
    private $credentials;
    private $responses = [
        'Traba, no te acostumbres',
        'BREAKING NEWS!',
        'Papi no estoy pa ti hoy',
        'Te me calmas, que son una pila, te me calmas.',
        'Bah, a esta hora y con este recado'
    ];

    public function __construct()
    {
        $this->credentials = array(
            'bearer_token' => config('twitter.bearer'), // OAuth 2.0 Bearer Token requests
            'consumer_key' => config('twitter.consumer_key'), // identifies your app, always needed
            'consumer_secret' => config('twitter.consumer_secret'), // app secret, always needed
            'token_identifier' => config('twitter.bot_id'), // OAuth 1.0a User Context requests
            'token_secret' => config('twitter.bot_secret'), // OAuth 1.0a User Context requests
        );

        $this->twitter = new BirdElephant($this->credentials);
    }

    /**
     * Look for new mentions and preccs it as a command
     */
    public function process()
    {
        // old and too much info search way
        $tweets = $this->twitter->user('newsthisbot')->mentions([
            'max_results' => 100,
            'expansions' => 'attachments.media_keys,author_id,entities.mentions.username,in_reply_to_user_id,referenced_tweets.id',
            'media.fields' => 'preview_image_url,url',
            // get mentions from a certain date to now
            'since_id' => "1537159637287555070"
        ]);

        // Is there any pendiing Tweet?
        if (isset($tweets->data) && count($tweets->data) > 0) {

            // Loop over every Tweet and pass to Command Controller
            foreach ($tweets->data as $tweet) {

                dump($tweet);

                $new_tweet = (new CommandController())->build($this->twitter, $tweet);

                // if $new_tweet is type of Tweet
                if ($new_tweet instanceof Tweet) {

                    dd($new_tweet);
                    
                    $response = $this->twitter->tweets()->tweet($new_tweet);

                    dump($response);

                    // Save referenced Tweet
                    TweetModel::insert([
                        'tweet_id' => $tweet->id,
                        'replied' => true,
                        'response_id' => $response->data->id
                    ]);
                    
                } else {

                    // Do nothing
                    echo $tweet->id . ": Not valid command";
                }
            }
        } else {
            return response()->json([
                'message' => 'No new tweets'
            ]);
        }
    }

    /**
     * Parse tweet to see its info
     * Debug purposes
     */
    public function parse_tweet()
    {
        $tweet = $this->twitter->tweets()->get('1537144978446483457', [
            'expansions' => 'attachments.media_keys,author_id,entities.mentions.username,in_reply_to_user_id,referenced_tweets.id',
            'media.fields' => 'preview_image_url,url',
        ]);

        // try to get the image object and download it
        if (isset($tweet->includes->media[0]->type) && $tweet->includes->media[0]->type == 'photo' && isset($tweet->includes->media[0]->url)) {

            $image = $tweet->includes->media[0]->url;

            // Now save this image into filesystem
            $path = $image;
            $filename = basename($path);

            Image::make($path)->save(public_path('images/' . $filename));
        }

        dd($tweet);
    }

    /**
     * Welcome page for the application.
     */
    public function index(Request $request)
    {
        // $connection = new TwitterOAuth(config('twitter.consumer_key'), config('twitter.consumer_secret'), config('twitter.bot_id'), config('twitter.bot_secret'));
        // $content = $connection->get("account/verify_credentials");

        // Need the Tweet permission
        // $tweet = new Tweet();
        // $tweet->text('¿Osea que ya puedo trollear a @Bachecubano?');
        // dump($this->twitter->tweets()->tweet($tweet));

        //return view('welcome');
    }

    /**
     * Manual Query to test stuff
     * 'Raw' access for those who prefer to control all the
     * variables in exchange for a lack of convenience.
     *
     * @param array $credentials - array of credentials
     * @param string $http_method - 'GET'|'POST|'PUT'|'DELETE'
     * @param string $endpoint - the endpoint you want to call
     * @param array $params - query parameters
     * @param array|null $data - post/put data
     * @param boolean $stream - streaming endpoint if true, default false
     * @param boolean $signed - bearer auth or user context auth, default bearer
     * @return object
     */
    public function manual(Request $request)
    {
        // get Erich Garcia Profile ID: 1418506398
        $response = $this->twitter->call($this->credentials, 'GET', 'users/by/username/erichgarciacruz', [], null, false, false);

        dump($response);

        $response = $this->twitter->call($this->credentials, 'GET', 'users/1418506398/mentions', ['max_results' => 5, 'tweet.fields' => 'attachments,author_id,created_at,public_metrics,source', 'expansions' => 'attachments.media_keys'], null, false, false);

        dump($response);

        /*
        // get Jack timeline
        $response = $this->twitter->call($this->credentials, 'GET', 'users/by/username/jack', [], null, false, false);
        $tw_id = $response->data->id;
        $response = $this->twitter->call($this->credentials, 'GET', 'users/' . $tw_id . '/tweets', [], null, false, false);

        dump($response);

        // get Elon Mentions
        $response = $this->twitter->call($this->credentials, 'GET', 'users/by/username/elonmusk', [], null, false, false);
        $tw_id = $response->data->id;
        $response = $this->twitter->call($this->credentials, 'GET', 'users/' . $tw_id . '/mentions', [], null, false, false);

        dump($response);
        */
    }

    /**
     * Generic classes
     */
    public function followers(Request $request)
    {
        dump($this->twitter);

        //get a user's followers using the handy helper methods
        $followers = $this->twitter->user('newsthisbot')->followers([
            'max_results' => 20,
            'user.fields' => 'profile_image_url'
        ]);

        dump($followers);

        //pass your query params to the methods directly
        $following = $this->twitter->user('newsthisbot')->following([
            'max_results' => 20,
            'user.fields' => 'profile_image_url'
        ]);

        dump($following);

        foreach ($following->data as $follower) {
            echo "<div>";
            echo "<img src='{$follower->profile_image_url}' alt='{$follower->name}'/>";
            echo "<h3>{$follower->name}</h3>";
            echo "</div>";
        }
    }

    /**
     * Me Endpoint
     * The Bot ID: 825895109292126208
     */
    public function user(Request $request)
    {
        $user_name = "newsthisbot";
        $user = $this->twitter->user($user_name);

        dump($user);

        $params = [
            'max_results' => 50,
            'expansions' => 'author_id,referenced_tweets.id,in_reply_to_user_id,attachments.media_keys,entities.mentions.username',
        ];

        dump($user->mentions($params));
    }

    /**
     * Get user mentions
     * expansions: attachments.poll_ids, attachments.media_keys, author_id, entities.mentions.username, geo.place_id, in_reply_to_user_id, referenced_tweets.id, referenced_tweets.id.author_id
     * media.fields: duration_ms, height, media_key, preview_image_url, type, url, width, public_metrics, non_public_metrics, organic_metrics, promoted_metrics, alt_text, variants
     * tweet.fields: attachments, author_id, context_annotations, conversation_id, created_at, entities, geo, id, in_reply_to_user_id, lang, non_public_metrics, public_metrics, organic_metrics, promoted_metrics, possibly_sensitive, referenced_tweets, reply_settings, source, text, withheld
     * 
     * So, if author_id is different from 825895109292126208 (myself), it's a reply
     * 
     */
    public function mentions()
    {
        // old and too much info search way
        $tweets = $this->twitter->user('newsthisbot')->mentions([
            'max_results' => 100,
            'expansions' => 'author_id,referenced_tweets.id,in_reply_to_user_id,attachments.media_keys',
            'media.fields' => 'preview_image_url,url'
        ]);

        //dump($tweets->data);
        //dump($tweets->includes);
        //dump($tweets->errors);
        //dump($tweets->meta);

        // Check if you have mentions
        if (isset($tweets->data) && count($tweets->data) > 0) {

            // Iterate over found mentions
            foreach ($tweets->data as $tweet) {

                // Data from this object:
                // entities (mentions, hashtags, symbols, urls, media), text, author_id, id, referenced_tweets (array[0] = [id, type]), in_reply_to_user_id

                // get referenced Tweet condition, dont reply to direct tweets, always as comment of another tweet
                if (isset($tweet->referenced_tweets[0]->type) && $tweet->referenced_tweets[0]->type == 'replied_to' && isset($tweet->referenced_tweets[0]->id)) {

                    // Explode the tweet mention data
                    $exploded = explode(" ", $tweet->text);
                    $handle_position = array_search("@newsthisbot", $exploded);
                    $command = isset($exploded[$handle_position + 1]) ? $exploded[$handle_position + 1] : "";

                    // check if the command is valid
                    if (in_array($command, $this->commands)) {

                        echo "<h2>Command</h2>";
                        echo $command;

                        // get referenced Tweet
                        $referenced_tweet = $this->twitter->tweets()->get($tweet->referenced_tweets[0]->id);
                        dump($referenced_tweet);

                        // Reply to the referenced Tweet $referenced_tweet->data->id
                        echo "<h2>Tweet ID</h2>";
                        echo $tweet->id;
                        echo "<h2>Referenced Tweet ID</h2>";
                        echo $referenced_tweet->data->id;

                        // Look for the tweet_id on DB and see if its replied or not
                        $replied = TweetModel::where('tweet_id', $tweet->id)->first();
                        if (!$replied) {

                            // Now reply, save $tweet->id into DB for no more responses to it
                            $reply = (new Reply)->inReplyToTweetId($tweet->id);

                            dump($reply);

                            //$tweet = (new Tweet)->text($referenced_tweet->data->text)->reply($reply);
                            $new_tweet = (new Tweet)->text($this->responses[rand(0, count($this->responses) - 1)])->reply($reply);

                            dump($new_tweet);

                            // Send it!...
                            $response = $this->twitter->tweets()->tweet($new_tweet);

                            dump($response);

                            // Save referenced Tweet
                            TweetModel::insert([
                                'tweet_id' => $tweet->id,
                                'replied' => true,
                                'response_id' => $response->data->id
                            ]);

                        } else {
                            echo "<h2>Already Replied</h2>";
                        }
                    }
                }
            }
        }
    }

    /**
     * Tweet something with the logged in account
     */
    public function tweet()
    {
        // Need the Tweet permission
        $tweet = new Tweet();
        $tweet->text('Hello World! 👋');
        $this->twitter->tweets()->tweet($tweet);
    }
}
