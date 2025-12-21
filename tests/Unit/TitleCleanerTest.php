<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use App\Jobs\ProcessPlaylistTransfer;

class TitleCleanerTest extends TestCase
{
    /**
     * @dataProvider titleProvider
     */
    public function test_clean_youtube_title($original, $expected)
    {
        // Mock job dependencies
        $job = new ProcessPlaylistTransfer(1, 1);

        // Use reflection to access private method
        $method = new ReflectionMethod(ProcessPlaylistTransfer::class, 'cleanYoutubeTitle');
        $method->setAccessible(true);

        $cleaned = $method->invoke($job, $original);

        $this->assertEquals($expected, $cleaned);
    }

    public static function titleProvider()
    {
        return [
            ['Shape of You (Official Video)', 'Shape of You'],
            ['Shape of You (Official Audio)', 'Shape of You'],
            ['Shape of You [Official Video]', 'Shape of You'],
            ['Shape of You (Lyrics)', 'Shape of You'],
            ['Shape of You (Lyric Video)', 'Shape of You'],
            ['Shape of You - Ed Sheeran (Official Video)', 'Shape of You - Ed Sheeran'],
            ['Song Name [4K]', 'Song Name'],
            ['Song Name (Live)', 'Song Name'],
            ['Song Name (Live at Wembley)', 'Song Name'],
            ['Song Name | Official Video', 'Song Name'],
            ['Just A Normal Song', 'Just A Normal Song'],
            ['Song (feat. Artist)', 'Song'],
            ['Song (ft. Artist)', 'Song'],
            ['Song ft. Artist', 'Song ft. Artist'],
            ['   Spaces   (Official)  ', 'Spaces'],
            // User provided failing cases
            ['Mogwai // Coolverine (Official Video)', 'Mogwai - Coolverine'],
            ['Boards Of Canada - Open The Light (HQ) Video', 'Boards Of Canada - Open The Light'],
            ['Trees of Eternity - Sinking Ships (demo version) Promo Video', 'Trees of Eternity - Sinking Ships'],
            ['Boards of Canada - Reach for the Dead (from Tomorrow\'s Harvest)', 'Boards of Canada - Reach for the Dead'],
            ['Mogwai - I\'m Jim Morrison, I\'m Dead', 'Mogwai - I\'m Jim Morrison, I\'m Dead'],
            ['ALEAH   VAPOUR ACOUSTIC VERSION OFFICIAL VIDEO', 'ALEAH VAPOUR'],
            ['THE GATHERING - Leaves (OFFICIAL VIDEO)', 'THE GATHERING - Leaves'],
            ['Pilgrim\'s Rest - Windhand Cover', 'Pilgrim\'s Rest'],
        ];
    }
}
