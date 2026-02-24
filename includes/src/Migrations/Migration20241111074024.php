<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241111074024
 */
class Migration20241111074024 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'tnt';
    }

    public function getDescription(): string
    {
        return 'video accessibility lang vars';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'errorMessages',
            'videoTagNotSupported',
            'Das HTML5 video-Tag wird von Ihrem Browser nicht unterstützt.'
            . ' Sie können das Video <a href="%s">herunterladen</a> und es in ihrem bevorzugtem Player abspielen.'
        );
        $this->setLocalization(
            'eng',
            'errorMessages',
            'videoTagNotSupported',
            'Your browser does not support the HTML5 video-tag.'
            . ' You can <a href="%s">download</a> the video and play it in your preferred player.'
        );
        $this->setLocalization(
            'ger',
            'media',
            'showVideoTranscript',
            'Transkript zum Video anzeigen'
        );
        $this->setLocalization(
            'eng',
            'media',
            'showVideoTranscript',
            'Show video transcript'
        );
        $this->setLocalization(
            'ger',
            'media',
            'showVideoTranscriptPopup',
            'Transkript öffnen'
        );
        $this->setLocalization(
            'eng',
            'media',
            'showVideoTranscriptPopup',
            'Open transcript'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->setLocalization(
            'ger',
            'errorMessages',
            'videoTagNotSupported',
            'Das HTML5 video-Tag wird von Ihrem Browser nicht unterstützt.'
        );
        $this->setLocalization(
            'eng',
            'errorMessages',
            'videoTagNotSupported',
            'Your browser does not support the HTML5 video-tag.'
        );
        $this->removeLocalization(
            'showVideoTranscript',
            'media',
        );
        $this->removeLocalization(
            'showVideoTranscriptPopup',
            'media',
        );
    }
}
