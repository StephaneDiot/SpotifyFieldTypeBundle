<?php

namespace SpotifyFieldTypeBundle\Spotify;

class SpotifyClient implements SpotifyClientInterface
{
    private $token;
    private $url;

    /**
     * Token is transfered from the service, we need a valid token (expire after 1 hour) each time we save a content using a Spotify URL. Here is just a demo for the training
     * SpotifyClient constructor.
     * @param $token
     */
    public function __construct($token )
    {
        $this->token = $token;
    }

    /**
     * Get the spotify username and playlistID from the URL entered by the user and generate a valid api URL to retrieve data from the Spotify API. After fetching the data , we will return some of them to be stored in the spotify fieldType
     * @param $playlistUrl
     * @return array
     */
    public function getEmbed( $playlistUrl )
    {

        preg_match( '#^https?://open.spotify\.com/user/([^/]+)/playlist/([0-9_A-Z_a-z]+)$#', (string)$playlistUrl, $matches );
        $this->url = 'https://api.spotify.com/v1/users/'.$matches[1].'/playlists/'.$matches[2];
        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization:'.$this->token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL,$this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result);
        $playListImg = '';
        if($response->images) {
            $img = $response->images;
            $playListImg = $img[0]->url;
        }
        //PS: OBJECT ARE NOT ALLOWED TO BE SAVED IN THE DATABASE USING THE FIELDTYPE API !! TRANSFORM ALL DATA TO STRING OR ARRAY
        $data = [
            'name' => $response->name ,
            'followers' => $response->followers->total ? $response->followers->total:'',
            'link' => $response->external_urls->spotify ? $response->external_urls->spotify : '',
            'image' => $playListImg ? $playListImg:'' ,
            'tracks' => $response->tracks->items ? json_decode(json_encode($response->tracks->items),true) : ''
        ];

        return $data;

    }


}