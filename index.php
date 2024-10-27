<?php
function cleanMovieTitle($title) {
    $title = pathinfo($title, PATHINFO_FILENAME);
    $title = str_replace(['.', '_'], ' ', $title);
    $title = preg_replace('/(?<!^)(?=[A-Z])/', ' ', $title);
    $title = trim(preg_replace('/\s+/', ' ', $title));
    return $title;
}

function getMovieData($title) {
    $apiKey = '4b94f857a0c0c333c98dbd3e1a937e85';
    $searchUrl = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&query=" . urlencode($title);
    $response = @file_get_contents($searchUrl);
    
    if($response) {
        $data = json_decode($response, true);
        if(isset($data['results']) && count($data['results']) > 0) {
            $movie = $data['results'][0];
            return [
                'title' => $movie['title'],
                'year' => substr($movie['release_date'], 0, 4),
                'poster' => $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] : null,
                'category' => $movie['genre_ids'],
                'rating' => isset($movie['vote_average']) ? $movie['vote_average'] / 2 : 0
            ];
        }
    }
    return null;
}

function getGenres() {
    $apiKey = '4b94f857a0c0c333c98dbd3e1a937e85';
    $genresUrl = "https://api.themoviedb.org/3/genre/movie/list?api_key={$apiKey}&language=de";
    $response = @file_get_contents($genresUrl);
    
    if($response) {
        $data = json_decode($response, true);
        $genres = [];
        foreach($data['genres'] as $genre) {
            $genres[$genre['id']] = $genre['name'];
        }
        return $genres;
    }
    return [];
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

function renderMovieCard($movie) {
    $movieUrl = 'movie.php?query=' . urlencode($movie['title']);
    echo '<a href="' . $movieUrl . '" class="movie-card">';
    if($movie['poster']) {
        echo '<img src="' . $movie['poster'] . '" alt="' . htmlspecialchars($movie['title']) . '" class="movie-poster">';
    } else {
        echo '<img src="/api/placeholder/200/300" alt="No poster available" class="movie-poster">';
    }
    echo '<div class="movie-info">';
    echo '<h3 class="movie-title">' . htmlspecialchars($movie['title']) . '</h3>';
    echo '<p class="movie-year">' . htmlspecialchars($movie['year']) . '</p>';
    echo renderStarRating($movie['rating']);
    echo '</div>';
    echo '</a>';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FireStream</title>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            background-color: var(--main-color);
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

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 0;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            background-color: var(--hover-color);
            color: var(--text-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            right: 0;
            opacity: 0;
            pointer-events: none;
        }

        .search-input.active {
            width: 300px;
            opacity: 1;
            pointer-events: all;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            z-index: 2;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .category-title {
            font-size: 1.5rem;
            margin: 2rem 0 1rem 0;
            padding-left: 2rem;
            color: var(--text-color);
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
            padding: 0 2rem 2rem 2rem;
        }

        .categories-container {
            padding-top: 5rem;
        }

        .movie-card {
            background-color: var(--hover-color);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-color);
            display: block;
        }

        .movie-card:hover {
            transform: translateY(-5px);
        }

        .movie-poster {
            width: 100%;
            aspect-ratio: 2/3;
            object-fit: cover;
        }

        .movie-info {
            padding: 1rem;
        }

        .movie-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .movie-year {
            font-size: 0.9rem;
            color: #888;
        }

        .rating {
            color: #ffd700;
            margin-top: 0.5rem;
        }

        .rating .fas,
        .rating .far {
            font-size: 0.9rem;
        }

        .search-results {
            padding: 6rem 2rem 2rem 2rem;
        }

        .search-header {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #888;
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .search-input.active {
                width: 200px;
            }

            .movie-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
                padding: 0 1rem 1rem 1rem;
            }

            .category-title {
                padding-left: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">FireStream</a>
        <div class="nav-icons">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Suchen...">
                <i class="fas fa-search icon search-icon"></i>
            </div>
            <i class="fas fa-user icon"></i>
            <i class="fas fa-cog icon"></i>
        </div>
    </header>

    <main>
        <?php
        $moviesDir = 'movies/';
        $movies = scandir($moviesDir);
        $moviesByCategory = [];
        $genres = getGenres();

        if(isset($_GET['search']) && !empty($_GET['search'])) {
            $searchQuery = htmlspecialchars($_GET['search']);
            echo '<div class="search-results">';
            echo '<div class="search-header">Suchergebnisse für: ' . $searchQuery . '</div>';
            echo '<div class="movie-grid">';
            
            foreach($movies as $movie) {
                if($movie != '.' && $movie != '..') {
                    $movieTitle = cleanMovieTitle($movie);
                    if(stripos($movieTitle, $searchQuery) !== false) {
                        $movieData = getMovieData($movieTitle);
                        if($movieData) {
                            renderMovieCard($movieData);
                        }
                    }
                }
            }
            echo '</div></div>';
        } else {
            foreach($movies as $movie) {
                if($movie != '.' && $movie != '..') {
                    $movieTitle = cleanMovieTitle($movie);
                    $movieData = getMovieData($movieTitle);
                    
                    if($movieData) {
                        foreach($movieData['category'] as $categoryId) {
                            if(isset($genres[$categoryId])) {
                                $categoryName = $genres[$categoryId];
                                if(!isset($moviesByCategory[$categoryName])) {
                                    $moviesByCategory[$categoryName] = [];
                                }
                                $moviesByCategory[$categoryName][] = $movieData;
                            }
                        }
                    }
                }
            }

            echo '<div class="categories-container">';
            foreach($moviesByCategory as $category => $categoryMovies) {
                echo '<h2 class="category-title">' . htmlspecialchars($category) . '</h2>';
                echo '<div class="movie-grid">';
                foreach($categoryMovies as $movie) {
                    renderMovieCard($movie);
                }
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </main>

    <script>
        const searchIcon = document.querySelector('.search-icon');
        const searchInput = document.querySelector('.search-input');
        let searchTimeout;
        
        searchIcon.addEventListener('click', () => {
            searchInput.classList.toggle('active');
            if(searchInput.classList.contains('active')) {
                searchInput.focus();
            }
        });

        document.addEventListener('click', (e) => {
            if(!e.target.closest('.search-container')) {
                searchInput.classList.remove('active');
            }
        });

        searchInput.addEventListener('keyup', (e) => {
            clearTimeout(searchTimeout);
            
            if(e.key === 'Enter') {
                window.location.href = `index.php?search=${encodeURIComponent(searchInput.value)}`;
                return;
            }

            searchTimeout = setTimeout(() => {
                if(searchInput.value.length >= 2) {
                    window.location.href = `index.php?search=${encodeURIComponent(searchInput.value)}`;
                }
            }, 500);
        });

        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');
        if(searchQuery) {
            searchInput.value = searchQuery;
            searchInput.classList.add('active');
        }
    </script>
</body>
</html>