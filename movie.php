<?php
function getMovieDetails($title) {
    $apiKey = '4b94f857a0c0c333c98dbd3e1a937e85';
    $searchUrl = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&query=" . urlencode($title);
    $response = @file_get_contents($searchUrl);
    
    if($response) {
        $data = json_decode($response, true);
        if(isset($data['results']) && count($data['results']) > 0) {
            $movie = $data['results'][0];
            $movieId = $movie['id'];
            $detailsUrl = "https://api.themoviedb.org/3/movie/{$movieId}?api_key={$apiKey}&language=de";
            $detailsResponse = @file_get_contents($detailsUrl);
            
            if($detailsResponse) {
                $details = json_decode($detailsResponse, true);
                $cleanFileName = $details['title'];
                $cleanFileName = preg_replace('/[:\?\"\'\/\\\\]/', '', $cleanFileName);
                $cleanFileName = strtolower($cleanFileName);
                return [
                    'title' => $details['title'],
                    'clean_filename' => $cleanFileName,
                    'original_title' => $details['original_title'],
                    'year' => substr($details['release_date'], 0, 4),
                    'poster' => $details['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $details['poster_path'] : null,
                    'backdrop' => $details['backdrop_path'] ? "https://image.tmdb.org/t/p/original" . $details['backdrop_path'] : null,
                    'overview' => $details['overview'],
                    'rating' => $details['vote_average'] / 2,
                    'runtime' => $details['runtime'],
                    'genres' => $details['genres']
                ];
            }
        }
    }
    return null;
}
function renderStarRating($rating) {
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - ceil($rating);
    
    $html = '<div class="rating">';
    for($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if($hasHalfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    $html .= '</div>';
    return $html;
}

$movieTitle = isset($_GET['query']) ? $_GET['query'] : '';
$movieData = getMovieDetails($movieTitle);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movieData['title']); ?> - FireStream</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/assets/icons/logo.ico" type="image/x-icon">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Fire Stream offers a wide selection of movies and series for every taste. Enjoy the best streaming experience with our user-friendly platform.">
    <meta name="keywords" content="Streaming, Movies, Series, Fire Stream, Online Streaming, Entertainment">
    <meta name="author" content="Fire Stream Team">
    
    <!-- Open Graph Meta Tags for Social Media -->
    <meta property="og:title" content="Fire Stream - Your Streaming Service for Movies and Series">
    <meta property="og:description" content="Discover the latest movies and series on Fire Stream. Subscribe now and start streaming immediately!">
    <meta property="og:image" content="/images/logo.png">
    <meta property="og:url" content="https://www.firestream.com">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Fire Stream - Your Streaming Service for Movies and Series">
    <meta name="twitter:description" content="Discover the latest movies and series on Fire Stream. Subscribe now and start streaming immediately!">
    <meta name="twitter:image" content="/images/logo.png">
    
    <!-- Additional Meta Tags -->
    <link rel="canonical" href="https://www.firestream.com">
    <style>
        :root {
            --main-color: #0D0907;
            --text-color: #ffffff;
            --hover-color: #1a1511;
            --accent-color: #2a2521;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: var(--main-color);
            color: var(--text-color);
        }
        body::-webkit-scrollbar {
            display: none;
        }

        body {
            -ms-overflow-style: none; 
            overflow-y: scroll; 
        }

        body {
            scrollbar-width: none; 
            overflow-y: scroll; 
        }

        .scrollable-element::-webkit-scrollbar {
            display: none; 
        }


        .scrollable-element {
            -ms-overflow-style: none; 
            overflow-y: scroll; 
        }


        .scrollable-element {
            scrollbar-width: none; 
            overflow-y: scroll; 
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            background-color: rgba(13, 9, 7, 0.9);
        }
        .volume-control {
    position: relative;
    display: flex;
    align-items: center;
}
.volume-slider-container {
    width: 0;
    height: 40px;
    overflow: hidden;
    transition: width 0.3s;
    display: flex;
    align-items: center;
    margin-left: 10px;
}
.volume-control:hover .volume-slider-container {
    width: 100px;
}

.volume-slider {
    width: 100px;
    height: 4px;
    -webkit-appearance: none;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    outline: none;
}
.volume-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    cursor: pointer;
}

.volume-slider::-moz-range-thumb {
    width: 12px;
    height: 12px;
    background: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
}


.preview-container {
    position: absolute;
    bottom: 100%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    padding: 4px;
    border-radius: 4px;
    display: none;
}

.preview-time {
    color: white;
    font-size: 12px;
    text-align: center;
    margin-top: 4px;
}

.preview-progress {
    position: absolute;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    pointer-events: none;
    display: none;
}

.video-progress {
    position: relative;
}
.preview-thumbnail {
    width: 160px;
    height: 90px;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: var(--text-color);
        }

        .nav-icons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .icon {
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .icon:hover {
            color: #666;
        }

        .movie-hero {
            position: relative;
            width: 100%;
            height: 70vh;
            overflow: hidden;
            background-image: url(<?php echo htmlspecialchars($movieData['backdrop']); ?>);
    background-size: cover;
    background-repeat: no-repeat;

        }

        .backdrop-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            
            
        }

        .movie-content {
            position: relative;
            padding: 2rem;
            margin-top: -150px;
            z-index: 2;
        }

        .movie-info {
            max-width: 800px;
            background-color: rgba(13, 9, 7, 0.9);
            padding: 2rem;
            border-radius: 10px;
        }

        .movie-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .movie-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            color: #888;
        }

        .movie-description {
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #e50914;
            color: white;
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .video-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: black;
            z-index: 2000;
            display: none;
        }

        .video-player {
            width: 100%;
            height: 100%;
        }

        .video-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
    transition: opacity 0.3s ease;
    z-index: 2002; 
}

        .video-info {
    position: absolute;
    top: 0;
    left: 4rem; 
    right: 0;
    padding: 1rem;
    background: linear-gradient(rgba(0, 0, 0, 0.7), transparent);
    transition: opacity 0.3s ease;
}

        .video-title {
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .back-button {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 2001;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.5rem;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .video-progress {
            width: 100%;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.2);
            margin-bottom: 1rem;
            cursor: pointer;
        }

        .progress-bar {
            height: 100%;
            background-color: #e50914;
            width: 0;
        }

        .control-buttons {
    display: flex;
    align-items: center;
    gap: 1rem;
}

        .control-button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .control-button.fullscreen {
    margin-left: auto; 
}

        .hide-controls {
            opacity: 0;
        }

        .paused-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: none;
    z-index: 2001;
}

.paused-info {
    position: absolute;
    left: 2rem; 
    top: 50%;
    transform: translateY(-50%);
    text-align: left;
}

        .paused-info h2 {
    font-size: 3rem;
    margin: 0;
}

        .rating {
            color: #ffd700;
            margin-bottom: 1rem;
        }

        .rating .fas,
        .rating .far {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">FireStream</a>
        <div class="nav-icons">
            <i class="fas fa-user icon"></i>
            <i class="fas fa-cog icon"></i>
        </div>
    </header>

    <?php if($movieData): ?>
    <div class="movie-hero">

    </div>

    <div class="movie-content">
        <div class="movie-info">
            <h1 class="movie-title"><?php echo htmlspecialchars($movieData['title']); ?></h1>
            <div class="movie-meta">
                <span><?php echo htmlspecialchars($movieData['year']); ?></span>
                <span><?php echo $movieData['runtime']; ?> Min.</span>
                <span>
                    <?php
                    $genres = array_map(function($genre) {
                        return $genre['name'];
                    }, $movieData['genres']);
                    echo htmlspecialchars(implode(', ', $genres));
                    ?>
                </span>
            </div>
            <?php echo renderStarRating($movieData['rating']); ?>
            <p class="movie-description"><?php echo htmlspecialchars($movieData['overview']); ?></p>
            <div class="action-buttons">
                <button class="btn btn-primary" id="playButton">
                    <i class="fas fa-play"></i> Abspielen
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-download"></i> Download
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-share"></i> Teilen
                </button>
            </div>
        </div>
    </div>


    <div class="video-container">
    <button class="back-button">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="video-info">
        <h2 class="video-title"><?php echo htmlspecialchars($movieData['title']); ?></h2>
    </div>
    <video class="video-player">
        <source src="movies/<?php echo $movieData['clean_filename']; ?>.mp4" type="video/mp4">
        Ihr Browser unterstützt das Video-Tag nicht.
    </video>
    <div class="video-controls">
        <div class="video-progress">
            <div class="preview-progress"></div>
            <div class="progress-bar"></div>
            <div class="preview-container">
                <div class="preview-thumbnail">
                    Preview nicht verfügbar
                </div>
                <div class="preview-time"></div>
            </div>
        </div>
        <div class="control-buttons">
            <button class="control-button play-pause">
                <i class="fas fa-play"></i>
            </button>
            <div class="volume-control">
                <button class="control-button volume">
                    <i class="fas fa-volume-up"></i>
                </button>
                <div class="volume-slider-container">
                    <input type="range" class="volume-slider" min="0" max="100" value="100">
                </div>
            </div>
            <span class="time">0:00:00 / 0:00:00</span>
            <button class="control-button fullscreen">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </div>
    <div class="paused-overlay">
        <div class="paused-info">
            <h2><?php echo htmlspecialchars($movieData['title']); ?></h2>
        </div>
    </div>
</div>
    <?php else: ?>
        <div class="movie-content">
            <div class="movie-info">
                <h1>Film nicht gefunden</h1>
            </div>
        </div>
    <?php endif; ?>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const playButton = document.getElementById('playButton');
    const videoContainer = document.querySelector('.video-container');
    const video = document.querySelector('.video-player');
    const controls = document.querySelector('.video-controls');
    const videoInfo = document.querySelector('.video-info');
    const backButton = document.querySelector('.back-button');
    const playPauseButton = document.querySelector('.play-pause');
    const volumeButton = document.querySelector('.volume');
    const volumeSlider = document.querySelector('.volume-slider');
    const fullscreenButton = document.querySelector('.fullscreen');
    const progressBar = document.querySelector('.progress-bar');
    const videoProgress = document.querySelector('.video-progress');
    const timeDisplay = document.querySelector('.time');
    const pausedOverlay = document.querySelector('.paused-overlay');
    const previewContainer = document.querySelector('.preview-container');
    const previewTime = document.querySelector('.preview-time');
    const previewProgress = document.querySelector('.preview-progress');
    const previewThumbnail = document.querySelector('.preview-thumbnail');
    
    let hideControlsTimeout;
    let lastVolume = 1;
    let isControlsHovered = false;
    let lastCurrentTime = 0; 


    const updateStyles = `
        <style>
            .video-info, .back-button {
                z-index: 2003;
            }
            .paused-overlay {
                z-index: 2002;
            }
            .video-controls {
                z-index: 2003;
            }
        </style>
    `;
    document.head.insertAdjacentHTML('beforeend', updateStyles);

    function toggleControls(show) {
        const elements = [controls, videoInfo, backButton];
        elements.forEach(el => {
            el.classList.toggle('hide-controls', !show);
        });
    }

    function startHideControlsTimer() {
        if (isControlsHovered || video.paused) return;
        
        clearTimeout(hideControlsTimeout);
        toggleControls(true);
        hideControlsTimeout = setTimeout(() => {
            if (!video.paused && !isControlsHovered) {
                toggleControls(false);
            }
        }, 3000);
    }

    function formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    function updateVolumeIcon(volume) {
        if (volume === 0) {
            volumeButton.innerHTML = '<i class="fas fa-volume-mute"></i>';
        } else if (volume < 0.5) {
            volumeButton.innerHTML = '<i class="fas fa-volume-down"></i>';
        } else {
            volumeButton.innerHTML = '<i class="fas fa-volume-up"></i>';
        }
    }

    function updateVideoPreview(time) {
        const canvas = document.createElement('canvas');
        canvas.width = 160;
        canvas.height = 90;
        const ctx = canvas.getContext('2d');
        
        lastCurrentTime = video.currentTime;
        
        video.currentTime = time;
        
        return new Promise((resolve) => {
            video.addEventListener('seeked', function onSeeked() {
                video.removeEventListener('seeked', onSeeked);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                video.currentTime = lastCurrentTime;
                
                resolve(canvas.toDataURL());
            }, { once: true });
        });
    }

    videoContainer.addEventListener('mousemove', () => {
        toggleControls(true);
        startHideControlsTimer();
    });

    controls.addEventListener('mouseenter', () => {
        isControlsHovered = true;
        clearTimeout(hideControlsTimeout);
        toggleControls(true);
    });

    controls.addEventListener('mouseleave', () => {
        isControlsHovered = false;
        if (!video.paused) {
            startHideControlsTimer();
        }
    });

    playButton.addEventListener('click', () => {
        videoContainer.style.display = 'block';
        video.play();
        startHideControlsTimer();
    });

    backButton.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        }
        videoContainer.style.display = 'none';
        video.pause();
    });

    playPauseButton.addEventListener('click', () => {
        if (video.paused) {
            video.play();
            playPauseButton.innerHTML = '<i class="fas fa-pause"></i>';
            pausedOverlay.style.display = 'none';
            startHideControlsTimer();
        } else {
            video.pause();
            playPauseButton.innerHTML = '<i class="fas fa-play"></i>';
            pausedOverlay.style.display = 'block';
            toggleControls(true);
        }
    });

    let isPreviewSeeking = false;
    videoProgress.addEventListener('mousemove', async (e) => {
        const rect = videoProgress.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / videoProgress.offsetWidth;
        const previewTimeValue = Math.floor(pos * video.duration);
        
        previewContainer.style.display = 'block';

        previewContainer.style.left = `${e.clientX}px`;
        const timeString = formatTime(previewTimeValue);
        previewTime.textContent = timeString;
        
        previewProgress.style.display = 'block';
        previewProgress.style.width = `${pos * 100}%`;
        
        if (video.readyState >= 4 && !isPreviewSeeking) {
            isPreviewSeeking = true;
            try {
                const thumbnailImage = await updateVideoPreview(previewTimeValue);
                previewThumbnail.style.backgroundImage = `url(${thumbnailImage})`;
                previewThumbnail.style.backgroundSize = 'cover';
                previewThumbnail.textContent = '';
            } catch (error) {
                console.error('Preview generation failed:', error);
            }
            isPreviewSeeking = false;
        }
    });

    videoProgress.addEventListener('mouseout', () => {
        previewContainer.style.display = 'none';
        previewProgress.style.display = 'none';
    });

    videoProgress.addEventListener('click', (e) => {
        const rect = videoProgress.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / videoProgress.offsetWidth;
        video.currentTime = pos * video.duration;
    });

    volumeSlider.addEventListener('input', (e) => {
        const value = e.target.value / 100;
        video.volume = value;
        lastVolume = value;
        updateVolumeIcon(value);
    });

    volumeButton.addEventListener('click', () => {
        if (video.volume > 0) {
            lastVolume = video.volume;
            video.volume = 0;
            volumeSlider.value = 0;
        } else {
            video.volume = lastVolume;
            volumeSlider.value = lastVolume * 100;
        }
        updateVolumeIcon(video.volume);
    });

    fullscreenButton.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            videoContainer.requestFullscreen();
            fullscreenButton.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            document.exitFullscreen();
            fullscreenButton.innerHTML = '<i class="fas fa-expand"></i>';
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            fullscreenButton.innerHTML = '<i class="fas fa-expand"></i>';
        }
    });

    video.addEventListener('timeupdate', () => {
        const percentage = (video.currentTime / video.duration) * 100;
        progressBar.style.width = percentage + '%';
        timeDisplay.textContent = `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`;
    });

    video.addEventListener('play', () => {
        playPauseButton.innerHTML = '<i class="fas fa-pause"></i>';
        pausedOverlay.style.display = 'none';
        startHideControlsTimer();
    });

    video.addEventListener('pause', () => {
        playPauseButton.innerHTML = '<i class="fas fa-play"></i>';
        pausedOverlay.style.display = 'block';
        toggleControls(true);
    });

    document.addEventListener('keydown', (e) => {
        if (videoContainer.style.display === 'block') {
            switch(e.key.toLowerCase()) {
                case ' ':
                case 'k':
                    e.preventDefault();
                    if (video.paused) video.play();
                    else video.pause();
                    break;
                case 'f':
                    e.preventDefault();
                    if (!document.fullscreenElement) videoContainer.requestFullscreen();
                    else document.exitFullscreen();
                    break;
                case 'm':
                    e.preventDefault();
                    video.muted = !video.muted;
                    updateVolumeIcon(video.muted ? 0 : video.volume);
                    volumeSlider.value = video.muted ? 0 : video.volume * 100;
                    break;
                case 'arrowleft':
                    e.preventDefault();
                    video.currentTime = Math.max(0, video.currentTime - 10);
                    break;
                case 'arrowright':
                    e.preventDefault();
                    video.currentTime = Math.min(video.duration, video.currentTime + 10);
                    break;
            }
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && videoContainer.style.display === 'block') {
            if (!document.fullscreenElement) {
                videoContainer.style.display = 'none';
                video.pause();
            }
        }
    });

    document.querySelector('.btn-secondary:nth-child(3)').addEventListener('click', () => {
        if (navigator.share) {
            navigator.share({
                title: document.querySelector('.movie-title').textContent,
                text: document.querySelector('.movie-description').textContent,
                url: window.location.href
            }).catch(console.error);
        } else {
            const tempInput = document.createElement('input');
            document.body.appendChild(tempInput);
            tempInput.value = window.location.href;
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Link in die Zwischenablage kopiert!');
        }
    });

    document.querySelector('.btn-secondary:nth-child(2)').addEventListener('click', () => {
        const link = document.createElement('a');
        link.href = video.querySelector('source').src;
        link.download = video.querySelector('source').src.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
    </script>
</body>
</html>