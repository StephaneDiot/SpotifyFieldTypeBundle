<?php

namespace SpotifyFieldTypeBundle\Spotify;

interface SpotifyClientInterface
{

    public function getEmbed( $playlistUrl );
}