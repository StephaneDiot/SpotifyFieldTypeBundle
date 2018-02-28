<?php

namespace SpotifyFieldTypeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSpotifyContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezsystems:spotify-fieldtype:create-content' )
            ->setDefinition(
                array(
                    new InputArgument( 'title', InputArgument::REQUIRED, 'content title' ),
                     ))
            ->setDescription( "Creates a new Content of the spotify type" );


    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $title = $input->getArgument( 'title' );

        $url ='https://open.spotify.com/user/iamwillj91/playlist/1hMD8meMST75Ml0DNFBEID';
        //$url = 'https://open.spotify.com/user/spotify_germany/playlist/7gjTRaVp9UP2wpWewzvAYS';

        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $repository->setCurrentUser(
            $repository->getUserService()->loadUserByLogin( 'admin' )
        );

        $contentService = $repository->getContentService();

        // Content create struct
        $createStruct = $contentService->newContentCreateStruct(
            $repository->getContentTypeService()->loadContentTypeByIdentifier( 'spotify' ),
            'eng-GB'
        );
        $createStruct->setField( 'title', $title );
        $createStruct->setField( 'playlist', $url, 'eng-GB' );

        try
        {
            $contentDraft = $contentService->createContent(
                $createStruct,
                array( $repository->getLocationService()->newLocationCreateStruct( 2 ) )
            );
            $content = $contentService->publishVersion( $contentDraft->versionInfo );
            $output->writeln( "Created Content 'Spotify Playlist' with ID {$content->id}" );

            //print_r($content);
        }
        catch ( \Exception $e )
        {
            $output->writeln( "An error occured creating the content: " . $e->getMessage() );
            $output->writeln( $e->getTraceAsString() );
        }
    }
}
