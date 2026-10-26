<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\FileUpload;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Infolist\AudioEntry;
use Dskripchenko\LaravelAdmin\Infolist\VideoEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Layouts › Media players: the UI kit's video and audio players in the panel.
 *
 * - Video: an MP4 in two qualities with subtitles in two languages,
 *   chapters and thumbnails over the scrub bar; and the same clip as an HLS
 *   stream (fMP4, two qualities, two audio tracks) through the kit's adapter.
 * - Audio: a playlist in three formats with the waveform, an audio-only HLS
 *   stream and the mini player.
 * - A table of call recordings: a mini player per row — starting one pauses
 *   the others — and a video column that opens the player in a modal.
 * - An upload field: an audio or video file shows a player under its name;
 *   the infolist entries draw files of a record the same way.
 * - ```video and ```audio blocks in markdown.
 *
 * The media live in public/media/showcase: test patterns, tones and a
 * speech-like signal made by ffmpeg, about 2 MB in all.
 */
final class MediaPlayersScreen extends ShowcaseScreen
{
    private const MEDIA = '/media/showcase';

    public static function slug(): string
    {
        return 'showcase-layouts-media-players';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'play';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Media players');
    }

    public function description(): ?string
    {
        return __('Video with qualities, subtitles, chapters and HLS; audio playlists with a waveform; players in tables, infolists, uploads and markdown.');
    }

    public function query(mixed ...$params): array
    {
        $m = self::MEDIA;

        return [
            'recordings' => [
                ['caller' => 'Anna Petrova', 'topic' => __('Delivery date'), 'duration' => 9, 'recording' => "$m/call-1.mp3", 'video' => null],
                ['caller' => 'Ivan Sokolov', 'topic' => __('Refund request'), 'duration' => 7, 'recording' => "$m/call-2.mp3", 'video' => "$m/clip-360.mp4", 'poster' => "$m/clip-poster.jpg"],
                ['caller' => 'Maria Ivanova', 'topic' => __('Wrong size'), 'duration' => 11, 'recording' => "$m/call-3.ogg", 'video' => null],
                ['caller' => 'Oleg Smirnov', 'topic' => __('Address change'), 'duration' => 6, 'recording' => "$m/call-4.wav", 'video' => "$m/clip-720.mp4", 'poster' => null],
            ],
            'poster' => "$m/clip-poster.jpg",
            'upload' => null,
            'sample_call' => ['url' => "$m/call-1.mp3", 'name' => 'call-1.mp3', 'mime' => 'audio/mpeg'],
            'sample_clip' => "$m/clip-360.mp4",
        ];
    }

    protected function demo(): array
    {
        $m = self::MEDIA;

        return [
            Layout::tabs([
                __('Video') => [
                    Layout::block(__('MP4: qualities, subtitles, chapters, thumbnails'), [
                        Layout::video([
                            ['src' => "$m/clip-720.mp4", 'label' => '720p'],
                            ['src' => "$m/clip-360.mp4", 'label' => '360p'],
                        ])
                            ->title(__('Test pattern'))
                            ->poster("$m/clip-poster.jpg")
                            ->subtitles("$m/clip.en.vtt", 'en', 'English', default: true)
                            ->subtitles("$m/clip.ru.vtt", 'ru', 'Русский')
                            ->chapters("$m/clip.chapters.vtt")
                            ->thumbnails("$m/clip.thumbs.vtt")
                            ->resume('showcase-clip')
                            ->maxWidth(760),
                    ])->description(__('Hover the scrub bar for the chapter and the thumbnail; the gear has the speed and the quality, the CC button the subtitles. The position is remembered: reload the page and the clip resumes. Keys: Space, ←/→, J/L, M, F, C.')),
                    Layout::block(__('HLS stream'), [
                        Layout::video("$m/hls/master.m3u8")
                            ->poster("$m/clip-poster.jpg")
                            // The kit's adapter even where the browser plays HLS itself: the quality and audio-track menus come with it.
                            ->hlsOptions(['preferNative' => false])
                            ->maxWidth(760),
                    ])->description(__('fMP4 segments in two qualities and two audio renditions, played through the kit\'s adapter — loaded only when a stream appears. "Auto" follows the bandwidth; the audio track is switched in the settings.')),
                ],
                __('Audio') => [
                    Layout::block(__('Playlist with a waveform'), [
                        Layout::audio([
                            ['src' => "$m/track-1.mp3", 'title' => __('Morning walk'), 'artist' => __('Test tones'), 'duration' => 24],
                            ['src' => "$m/track-2.ogg", 'title' => __('Low tide'), 'artist' => __('Test tones'), 'duration' => 20],
                            ['src' => "$m/track-3.m4a", 'title' => __('Bright step'), 'artist' => __('Test tones'), 'duration' => 18],
                        ])->waveform()->maxWidth(760),
                    ])->description(__('MP3, Ogg/Opus and M4A. The waveform is decoded in the browser; the next track starts when one ends; Shift+N / Shift+P switch tracks.')),
                    Layout::block(__('Audio-only HLS'), [
                        Layout::audio("$m/hls/audio_en.m3u8")->title(__('Stream'))->artist('HLS · fMP4')->maxWidth(760),
                    ])->description(__('An audio rendition of the stream above, played on its own. The waveform is not drawn for streams.')),
                    Layout::block(__('Mini player'), [
                        Layout::audio("$m/call-1.mp3")->mini(),
                    ])->description(__('One line, for lists and narrow places.')),
                ],
                __('In a table') => [
                    Layout::rows([
                        RelationTable::make('recordings')->title(__('Call recordings'))->columns([
                            TableColumn::make('caller')->label(__('Caller')),
                            TableColumn::make('topic')->label(__('Topic')),
                            TableColumn::make('recording')->label(__('Recording'))->asAudio(),
                            TableColumn::make('video')->label(__('Screen'))->asVideo(posterColumn: 'poster'),
                            TableColumn::make('duration')->label(__('Seconds'))->align('right'),
                        ]),
                    ]),
                    Layout::markdown(__('`asAudio()` puts a mini player in each row: starting one pauses the one that was playing. `asVideo()` shows a tile — or the poster from another column — and opens the player in a modal; nothing is downloaded before the click. A click on a player never opens the row.')),
                ],
                __('Upload and view') => [
                    Layout::rows([
                        FileUpload::make('upload')
                            ->title(__('Audio or video file'))
                            ->accept('audio/*,video/*')
                            ->maxSize(20 * 1024)
                            ->help(__('Upload a recording or a clip: a player appears under its name.')),
                    ]),
                    Layout::infolist([
                        AudioEntry::make('sample_call')->label(__('Call (AudioEntry, mini)')),
                        AudioEntry::make('sample_call')->label(__('Call (AudioEntry, full, waveform)'))->full()->waveform(),
                        VideoEntry::make('sample_clip')->label(__('Clip (VideoEntry)'))->posterFrom('poster')->maxWidth(480),
                    ]),
                ],
                __('In markdown') => [
                    Layout::markdown(implode("\n\n", [
                        __('Fenced `video` and `audio` blocks — a URL, or a few lines of YAML — become players in any markdown of the panel and in the WYSIWYG editor:'),
                        "```video\nsrc: $m/clip-360.mp4\nposter: $m/clip-poster.jpg\ntracks:\n  - src: $m/clip.en.vtt\n    srclang: en\n    label: English\n```",
                        "```audio\nsrc: $m/track-2.ogg\ntitle: Low tide\nwaveform: true\n```",
                    ]))->card(),
                ],
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}
