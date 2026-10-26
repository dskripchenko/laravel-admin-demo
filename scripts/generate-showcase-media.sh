#!/usr/bin/env bash
#
# Generates the media of the "Layouts › Media players" showcase: test
# patterns, tones and a speech-like signal made by ffmpeg — no third-party
# content, nothing to license. About 2.5 MB in all.
#
#   ./generate-media.sh [out-dir]      # default: public/media/showcase
#
# Needs ffmpeg with libx264, libmp3lame and libopus (FFMPEG=/path/to/ffmpeg).
set -euo pipefail

FF="${FFMPEG:-ffmpeg}"
OUT="${1:-public/media/showcase}"
D=12  # seconds of video
mkdir -p "$OUT/hls"
cd "$OUT"
q() { "$FF" -hide_banner -loglevel error -y "$@"; }

# A melody: a note every half second from a pentatonic walk, with a soft decay.
melody() { # $1 base frequency, $2 seconds
  echo "aevalsrc='0.35*sin(2*PI*$1*pow(2,(mod(floor(t*2)*7,12))/12)*t)*(1-0.7*mod(t*2,1))|0.35*sin(2*PI*$1*pow(2,(mod(floor(t*2)*7,12))/12)*t)*(1-0.7*mod(t*2,1))':s=44100:d=$2"
}
# Speech-like: a voiced buzz with moving overtones, syllables at ~4 Hz, pauses between phrases.
speech() { # $1 pitch, $2 seconds, $3 phrase length
  echo "aevalsrc='0.22*(sin(2*PI*$1*t)+0.6*sin(2*PI*2*$1*t+sin(5*t))+0.35*sin(2*PI*3.1*$1*t)+0.2*sin(2*PI*4.7*$1*t+2*sin(3*t)))*pow(0.5+0.5*sin(2*PI*4.2*t+sin(1.3*t)),2)*gt(mod(t,$3),0.7)':s=22050:d=$2"
}

# ---------------- video: two qualities, poster, sprite ----------------
q -f lavfi -i "testsrc=size=1280x720:rate=25:duration=$D" -f lavfi -i "$(melody 330 $D)" \
  -c:v libx264 -profile:v main -level 3.1 -pix_fmt yuv420p -b:v 380k -maxrate 500k -bufsize 760k -g 50 -keyint_min 50 -sc_threshold 0 \
  -c:a aac -b:a 64k -ac 2 -movflags +faststart -shortest clip-720.mp4
q -i clip-720.mp4 -vf "scale=640:360,hue=s=0.35" \
  -c:v libx264 -profile:v main -level 3.0 -pix_fmt yuv420p -b:v 150k -maxrate 200k -bufsize 300k -g 50 -keyint_min 50 -sc_threshold 0 \
  -c:a copy -movflags +faststart clip-360.mp4
q -ss 2.5 -i clip-720.mp4 -frames:v 1 -vf scale=640:360 -q:v 6 clip-poster.jpg
q -i clip-720.mp4 -vf "fps=1,scale=160:90,tile=4x3" -frames:v 1 -q:v 7 clip-sprite.jpg

ts() { printf '00:00:%02d.000' "$1"; }
{
  echo "WEBVTT"; echo
  for i in $(seq 0 $((D - 1))); do
    echo "$(ts $i) --> $(ts $((i + 1)))"
    echo "clip-sprite.jpg#xywh=$(( (i % 4) * 160 )),$(( (i / 4) * 90 )),160,90"; echo
  done
} > clip.thumbs.vtt
cat > clip.chapters.vtt <<VTT
WEBVTT

00:00:00.000 --> 00:00:04.000
Opening

00:00:04.000 --> 00:00:08.000
Colour bars

00:00:08.000 --> 00:00:12.000
Closing
VTT
cat > clip.en.vtt <<VTT
WEBVTT

00:00:00.500 --> 00:00:03.500
This clip is a test pattern made by ffmpeg.

00:00:04.000 --> 00:00:07.500
The counter in the middle shows the seconds.

00:00:08.000 --> 00:00:11.500
Press C to hide the subtitles.
VTT
cat > clip.ru.vtt <<VTT
WEBVTT

00:00:00.500 --> 00:00:03.500
Этот ролик — испытательная таблица, сделанная ffmpeg.

00:00:04.000 --> 00:00:07.500
Счётчик в центре показывает секунды.

00:00:08.000 --> 00:00:11.500
Нажмите C, чтобы скрыть субтитры.
VTT

# ---------------- HLS: fMP4, two qualities, two audio renditions ----------------
hls() { # $1 input, $2 name, extra args...
  local in="$1" name="$2"; shift 2
  q -i "$in" "$@" -f hls -hls_time 4 -hls_playlist_type vod -hls_segment_type fmp4 \
    -hls_fmp4_init_filename "${name}_init.mp4" -hls_segment_filename "hls/${name}_%02d.m4s" "hls/${name}.m3u8"
}
hls clip-720.mp4 v720 -an -c:v copy
hls clip-360.mp4 v360 -an -c:v copy
q -f lavfi -i "$(melody 330 $D)" -c:a aac -b:a 64k -ac 2 audio-en.m4a
q -f lavfi -i "$(melody 247 $D)" -c:a aac -b:a 64k -ac 2 audio-ru.m4a
hls audio-en.m4a audio_en -c:a copy
hls audio-ru.m4a audio_ru -c:a copy
rm -f audio-en.m4a audio-ru.m4a
cat > hls/master.m3u8 <<M3U
#EXTM3U
#EXT-X-VERSION:7
#EXT-X-INDEPENDENT-SEGMENTS
#EXT-X-MEDIA:TYPE=AUDIO,GROUP-ID="aud",NAME="English",LANGUAGE="en",DEFAULT=YES,AUTOSELECT=YES,CHANNELS="2",URI="audio_en.m3u8"
#EXT-X-MEDIA:TYPE=AUDIO,GROUP-ID="aud",NAME="Русский",LANGUAGE="ru",DEFAULT=NO,AUTOSELECT=YES,CHANNELS="2",URI="audio_ru.m3u8"
#EXT-X-STREAM-INF:BANDWIDTH=520000,AVERAGE-BANDWIDTH=450000,RESOLUTION=1280x720,FRAME-RATE=25.000,CODECS="avc1.4d401f,mp4a.40.2",AUDIO="aud"
v720.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=240000,AVERAGE-BANDWIDTH=210000,RESOLUTION=640x360,FRAME-RATE=25.000,CODECS="avc1.4d401e,mp4a.40.2",AUDIO="aud"
v360.m3u8
M3U

# ---------------- audio: a playlist in three formats ----------------
q -f lavfi -i "$(melody 262 24)" -c:a libmp3lame -b:a 64k -ac 2 track-1.mp3
q -f lavfi -i "$(melody 196 20)" -c:a libopus -b:a 40k track-2.ogg
q -f lavfi -i "$(melody 294 18)" -c:a aac -b:a 64k -ac 2 track-3.m4a

# ---------------- recordings: speech-like calls for the table ----------------
q -f lavfi -i "$(speech 118 9 3.1)" -c:a libmp3lame -b:a 40k -ac 1 call-1.mp3
q -f lavfi -i "$(speech 205 7 2.4)" -c:a libmp3lame -b:a 40k -ac 1 call-2.mp3
q -f lavfi -i "$(speech 140 11 3.6)" -c:a libopus -b:a 24k call-3.ogg
q -f lavfi -i "$(speech 180 6 2.0)" -c:a pcm_s16le -ar 11025 -ac 1 call-4.wav

du -sh . | awk '{print "media/showcase: " $1}'
