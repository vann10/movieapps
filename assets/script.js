// Variabel global untuk menyimpan data film saat ini
let currentMovie = null;

// Ganti fungsi checkLoginStatus Anda dengan yang ini

function checkLoginStatus() {
    $.ajax({
        url: 'backend/check_session.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            // Ini akan berjalan JIKA server merespons dengan status 200 OK
            // dan JSON yang valid.
            console.log("AJAX Success! Response:", response); // Untuk debugging

            const navLinks = $('#nav-links');
            const welcomeContainer = $('#welcome-container');
            const favoriteActions = $('#favorite-actions');

            if (response && response.loggedIn) {
                welcomeContainer.html(`<h1 class="text-2xl font-bold text-yellow-500">Halo, ${response.username}!</h1>`);
                navLinks.html(`
                    <li><a href="index.html" class="hover:text-yellow-500">Home</a></li>
                    <li><a href="#sinopsis" class="hover:text-yellow-500">Sinopsis</a></li>
                    <li><a href="#details" class="hover:text-yellow-500">Detail</a></li>
                    <li><a href="#" id="cast-link" class="hover:text-yellow-500">Pemeran</a></li>
                    <li><a href="favorites.html" class="hover:text-yellow-500">Favorit Saya</a></li>
                    <li><a href="backend/logout.php" class="text-red-500 hover:text-red-400">Logout</a></li>
                `);
                favoriteActions.show();
            } else {
                welcomeContainer.html('');
                navLinks.html(`
                    <li><a href="index.html" class="hover:text-yellow-500">Home</a></li>
                    <li><a href="#sinopsis" class="hover:text-yellow-500">Sinopsis</a></li>
                    <li><a href="#details" class="hover:text-yellow-500">Detail</a></li>
                    <li><a href="#" id="cast-link" class="hover:text-yellow-500">Pemeran</a></li>
                    <li><a href="auth.html" class="text-green-500 hover:text-green-400">Login/Registrasi</a></li>
                `);
                favoriteActions.hide();
            }

            $('#cast-link').on('click', toggleCastDetails);
        },
        error: function(xhr, status, error) {
            // Ini akan berjalan JIKA terjadi error (404, 500, dll.)
            console.error("AJAX Error! Status:", status);
            console.error("Response Text:", xhr.responseText); // Ini sangat penting!
            console.error("Error Thrown:", error);

            // Beri tahu pengguna bahwa ada masalah
            $('#welcome-container').html('<p class="text-red-500">Gagal memuat status login. Periksa console (F12) untuk detail.</p>');
            $('#favorite-actions').hide();
        }
    });
}


function toggleCastDetails(e) {
    if (e) e.preventDefault();
    const $castDetails = $("#cast-details");
    if ($castDetails.is(":visible")) {
        $castDetails.slideUp();
    } else {
        $castDetails.slideDown();
    }
}


$(document).ready(function () {
    // Panggil fungsi untuk memeriksa status login saat halaman dimuat
    checkLoginStatus();

    const $darkModeToggle = $("#toggle-dark-mode");
    const $body = $("body");
    const $html = $("html");
    const $searchInput = $("#search-input");
    const $searchButton = $("#search-button");
    const $moviePoster = $("#movie-poster");
    const $castContainer = $("#cast-container");
    const apiKey = "f39c5ab2232b117332a00650f0364756"; // Ganti dengan API key Anda

    // Fungsi Dark/Light Mode
    function enableDarkMode() {
        $html.addClass("dark");
        $darkModeToggle.text("🌙");
        localStorage.setItem("darkMode", "enabled");
    }

    function disableDarkMode() {
        $html.removeClass("dark");
        $darkModeToggle.text("🔆");
        localStorage.setItem("darkMode", "disabled");
    }

    if (localStorage.getItem("darkMode") === "enabled") {
        enableDarkMode();
    }

    $darkModeToggle.on("click", function () {
        if ($html.hasClass("dark")) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    });

    // Muat film default
    loadDefaultMovie();

    function loadDefaultMovie() {
        const defaultMovieId = 807; // Se7en
        loadFilmDataById(defaultMovieId);
    }

    function loadFilmDataById(movieId) {
        const url = `https://api.themoviedb.org/3/movie/${movieId}?api_key=${apiKey}&append_to_response=credits,videos`;
        $.getJSON(url, function (data) {
            updatePageWithMovieData(data);
        }).fail(function() {
            alert("Gagal memuat data film. Periksa koneksi atau API key.");
        });
    }

    function searchAndLoadFilm(title) {
        const searchUrl = `https://api.themoviedb.org/3/search/movie?api_key=${apiKey}&query=${encodeURIComponent(title)}`;
        $.getJSON(searchUrl, function (searchData) {
            if (searchData.results && searchData.results.length > 0) {
                loadFilmDataById(searchData.results[0].id);
            } else {
                alert("Film tidak ditemukan!");
            }
        }).fail(function() {
            alert("Gagal mencari film.");
        });
    }
    
    function updatePageWithMovieData(data) {
        currentMovie = data;

        $("#main-title").text(data.title);
        $moviePoster.attr("src", data.poster_path ? `https://image.tmdb.org/t/p/w500${data.poster_path}` : 'https://placehold.co/500x750/1f2937/a3a3a3?text=No+Image');
        $("#sinopsis-text").text(data.overview || "Sinopsis tidak tersedia.");
        $("#tagline-text").html(`<em>${data.tagline || "Tagline tidak tersedia."}</em>`);

        const director = data.credits.crew.find((p) => p.job === "Director")?.name || "Tidak diketahui";
        const releaseYear = data.release_date ? data.release_date.split("-")[0] : "N/A";
        const genres = data.genres.map((g) => g.name).join(", ") || "Tidak diketahui";

        $("#movie-details-dynamic").html(`
            <li class="mb-2"><strong>Sutradara:</strong> ${director}</li>
            <li class="mb-2"><strong>Tahun Rilis:</strong> ${releaseYear}</li>
            <li class="mb-2"><strong>Genre:</strong> ${genres}</li>
        `);

        $("#footer-text").html(`&copy; ${releaseYear} ${data.title}. All rights reserved.`);

        $castContainer.empty();
        data.credits.cast.slice(0, 6).forEach((actor) => {
            const profileUrl = actor.profile_path ? `https://image.tmdb.org/t/p/w185${actor.profile_path}` : "https://placehold.co/185x278/1f2937/a3a3a3?text=No+Image";
            $castContainer.append(`
                <div class="text-center">
                    <img src="${profileUrl}" alt="${actor.name}" class="rounded-lg mx-auto mb-2" />
                    <h3 class="font-bold">${actor.name}</h3>
                    <p class="text-sm text-gray-400">as ${actor.character}</p>
                </div>
            `);
        });
        // Pastikan container cast menggunakan grid
        $castContainer.addClass("grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4");
    }

    $searchButton.on("click", function () {
        const title = $searchInput.val();
        if (title) {
            searchAndLoadFilm(title);
        } else {
            alert("Harap masukkan judul film!");
        }
    });
     $searchInput.on('keypress', function(e) {
        if(e.which == 13) { // Tombol Enter
            $searchButton.click();
        }
    });

    // Event untuk tombol "tambah ke favorit"
    $("#add-favorite").on("click", function () {
        if (currentMovie) {
            addToFavorites(currentMovie);
        } else {
            alert("Film belum dimuat.");
        }
    });

    function addToFavorites(movie) {
        $.ajax({
            url: "backend/favorites.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                movie_id: movie.id,
                title: movie.title,
                poster_path: movie.poster_path,
                status: "watchlist",
            }),
            dataType: "json",
            success: function (response) {
                if(response.success){
                    alert(response.message || "Film berhasil ditambahkan!");
                } else {
                    alert(response.error || "Gagal menambahkan film.");
                }
            },
            error: function (xhr) {
                 const response = xhr.responseJSON;
                 alert(response.error || "Terjadi kesalahan. Mungkin Anda sudah menambahkan film ini.");
            },
        });
    }
});
