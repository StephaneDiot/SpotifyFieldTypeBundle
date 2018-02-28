<?php

namespace SpotifyFieldTypeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSpotifyContentTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezsystems:spotify-fieldtype:create-contenttype' )
            ->setDescription( "Creates a new Content Type with a spotify field" );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $repository->setCurrentUser(
            $repository->getUserService()->loadUserByLogin( 'admin' )
        );

        $contentTypeService = $repository->getContentTypeService();

        $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' );

        // Content type create struct
        $createStruct = $contentTypeService->newContentTypeCreateStruct( 'spotify' );
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->nameSchema = '<title>';
        $createStruct->names = array(
            'eng-GB' => 'Spotify'
        );
        $createStruct->descriptions = array(
            'eng-GB' => 'Reference to a Spotify playlist',
        );
        // add a TextLine Field with identifier 'title'

        $titleFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct( 'title', 'ezstring' );
        $titleFieldCreateStruct->names = array( 'eng-GB' => 'Title');
        $titleFieldCreateStruct->descriptions = array( 'eng-GB' => 'PlayList description' );
        $titleFieldCreateStruct->fieldGroup = 'content';
        $titleFieldCreateStruct->position = 10;
        $titleFieldCreateStruct->isTranslatable = true;
        $titleFieldCreateStruct->isRequired = true;
        $titleFieldCreateStruct->isSearchable = true;
        $createStruct->addFieldDefinition( $titleFieldCreateStruct );

        // spotify FieldDefinition
        $spotifyFieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct( 'playlist', 'ezspotify' );
        $spotifyFieldDefinitionCreateStruct->names = array( 'eng-GB' => 'Playlist' );
        $spotifyFieldDefinitionCreateStruct->descriptions = array( 'eng-GB' => 'The Playlist' );
        $spotifyFieldDefinitionCreateStruct->fieldGroup = 'content';
        $spotifyFieldDefinitionCreateStruct->position = 20;
        $spotifyFieldDefinitionCreateStruct->isTranslatable = true;
        $spotifyFieldDefinitionCreateStruct->isRequired = true;
        $spotifyFieldDefinitionCreateStruct->isSearchable = false;

        // Add the field definition to the type create struct
        $createStruct->addFieldDefinition( $spotifyFieldDefinitionCreateStruct );

        try
        {
            $contentTypeDraft = $contentTypeService->createContentType( $createStruct, array( $contentTypeGroup ) );
            $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
            $contentType = $contentTypeService->loadContentTypeByIdentifier( 'spotify' );
            $output->writeln( "Created ContentType 'Spotify' with ID {$contentType->id}" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException $e )
        {
            $output->writeln( "An error occured creating the content type: " . $e->getMessage() );
        }
    }
}
