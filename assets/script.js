// Variabel global untuk menyimpan data film saat ini
let currentMovie = null;

$(document).ready(function () {
  const $darkModeToggle = $("#toggle-dark-mode");
  const $body = $("body");
  const $searchInput = $("#search-input");
  const $searchButton = $("#search-button");
  const $moviePoster = $("#movie-poster");
  const $movieTitle = $("#main-title");
  const $castContainer = $("#cast-container");
  const apiKey = "f39c5ab2232b117332a00650f0364756";

  // Dark mode toggle functionality
  function enableDarkMode() {
    $body.addClass("dark-mode");
    $darkModeToggle.text("🌙");
    $(".theme-sensitive").attr("data-theme", "dark");
  }

  function disableDarkMode() {
    $body.removeClass("dark-mode");
    $darkModeToggle.text("🔆");
    $(".theme-sensitive").attr("data-theme", "light");
  }

  if (localStorage.getItem("darkMode") === "enabled") {
    enableDarkMode();
  }

  $darkModeToggle.on("click", function () {
    $body.addClass("theme-transition");
    if ($body.hasClass("dark-mode")) {
      disableDarkMode();
      localStorage.setItem("darkMode", "disabled");
    } else {
      enableDarkMode();
      localStorage.setItem("darkMode", "enabled");
    }
    setTimeout(() => {
      $body.removeClass("theme-transition");
    }, 500);
  });

  // Load default movie on page load
  if ($("#search-button").length) {
    loadDefaultMovie();
  }

  function loadDefaultMovie() {
    const defaultMovieId = 807;
    const url = `https://api.themoviedb.org/3/movie/${defaultMovieId}?api_key=${apiKey}&append_to_response=credits`;

    $.getJSON(url, function (data) {
      // Simpan data film saat ini
      currentMovie = data;

      // Judul dan Poster
      $("#main-title").text(data.title);
      $moviePoster.attr(
        "src",
        `https://image.tmdb.org/t/p/w500${data.poster_path}`
      );

      // Sinopsis dan Tagline
      $("#sinopsis-text").text(data.overview);
      $("#tagline-text").html(`<em>${data.tagline || "Tidak tersedia."}</em>`);

      // Tahun Rilis, Genre, Sutradara, Penulis, Pemeran
      const director =
        data.credits.crew.find((p) => p.job === "Director")?.name ||
        "Tidak diketahui";
      const writers =
        data.credits.crew
          .filter((p) => p.job === "Writer" || p.department === "Writing")
          .map((p) => p.name)
          .join(", ") || "Tidak diketahui";
      const releaseYear = data.release_date
        ? data.release_date.split("-")[0]
        : "N/A";
      const genres = data.genres.map((g) => g.name).join(", ");
      const actors = data.credits.cast
        .slice(0, 5)
        .map((c) => c.name)
        .join(", ");

      $("#movie-details-dynamic").html(`
        <li><h3>Sutradara</h3> ${director}</li>
        <li><h3>Penulis</h3> ${writers}</li>
        <li><h3>Pemeran</h3> ${actors}</li>
        <li><h3>Tahun Rilis</h3> ${releaseYear}</li>
        <li><h3>Genre</h3> ${genres}</li>
      `);

      $("#footer-text").html(
        `&copy; ${releaseYear} ${data.title}. All rights reserved.`
      );

      // Pemeran (Cast Card)
      $castContainer.empty();
      data.credits.cast.slice(0, 5).forEach((actor) => {
        const profileUrl = actor.profile_path
          ? `https://image.tmdb.org/t/p/w185${actor.profile_path}`
          : "https://via.placeholder.com/185x278?text=No+Image";

        const card = `
          <div class="cast-card">
            <img src="${profileUrl}" alt="${actor.name}" />
            <h3>${actor.name}</h3>
            <p><strong>as ${actor.character}</strong></p>
          </div>
        `;
        $castContainer.append(card);
      });
    });
  }

  function loadFilmData(title) {
    const searchUrl = `https://api.themoviedb.org/3/search/movie?api_key=${apiKey}&query=${encodeURIComponent(
      title
    )}`;

    $.ajax({
      url: searchUrl,
      method: "GET",
      dataType: "json",
      success: function (searchData) {
        if (searchData.results && searchData.results.length > 0) {
          const movieId = searchData.results[0].id;
          const detailUrl = `https://api.themoviedb.org/3/movie/${movieId}?api_key=${apiKey}&append_to_response=credits`;

          $.ajax({
            url: detailUrl,
            method: "GET",
            dataType: "json",
            success: function (data) {
              currentMovie = data;

              $("#main-title").text(data.title);
              $moviePoster.attr(
                "src",
                `https://image.tmdb.org/t/p/w500${data.poster_path}`
              );
              $("#sinopsis-text").text(data.overview);

              const director =
                data.credits.crew.find((person) => person.job === "Director")
                  ?.name || "Tidak diketahui";
              const writers =
                data.credits.crew
                  .filter(
                    (person) =>
                      person.job === "Writer" || person.department === "Writing"
                  )
                  .map((p) => p.name)
                  .join(", ") || "Tidak diketahui";
              const releaseYear = data.release_date
                ? data.release_date.split("-")[0]
                : "N/A";
              const genres = data.genres.map((g) => g.name).join(", ");

              const dynamicDetails = `
                <li><h3>Sutradara</h3> ${director}</li>
                <li><h3>Penulis</h3> ${writers}</li>
                <li><h3>Pemeran</h3> ${data.credits.cast
                  .slice(0, 5)
                  .map((c) => c.name)
                  .join(", ")}</li>
                <li><h3>Tahun Rilis</h3> ${releaseYear}</li>
                <li><h3>Genre</h3> ${genres}</li>
              `;
              $("#movie-details-dynamic").html(dynamicDetails);

              $("#tagline-text").html(
                `<em>${data.tagline || "Tidak tersedia."}</em>`
              );
              $("#footer-text").html(
                `&copy; ${releaseYear} ${data.title}. All rights reserved.`
              );

              $castContainer.empty();
              data.credits.cast.slice(0, 5).forEach((actor) => {
                const profileUrl = actor.profile_path
                  ? `https://image.tmdb.org/t/p/w185${actor.profile_path}`
                  : "https://via.placeholder.com/185x278?text=No+Image";

                const card = `
                  <div class="cast-card">
                    <img src="${profileUrl}" alt="${actor.name}" />
                    <h3>${actor.name}</h3>
                    <p><strong>as ${actor.character}</strong></p>
                  </div>
                `;
                $castContainer.append(card);
              });
            },
            error: function (xhr, status, error) {
              console.error("Error fetching movie details:", error);
              alert(`Gagal memuat detail film: ${error}`);
            },
          });
        } else {
          alert("Film tidak ditemukan!");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error searching for movie:", error);
        alert(`Gagal mencari film: ${error}`);
      },
    });
  }

  $searchButton.on("click", function () {
    const title = $searchInput.val();
    if (title) {
      loadFilmData(title);
    } else {
      alert("Harap masukkan judul film!");
    }
  });

  // Toggle cast details visibility
  const $castLink = $("#cast-link");
  const $castDetails = $("#cast-details");
  $castLink.on("click", function (e) {
    e.preventDefault();
    if ($castDetails.hasClass("visible")) {
      $castDetails.hide().removeClass("visible");
    } else {
      $castDetails.show();
      $castDetails.addClass("visible");
    }
  });

  // Add poster container styling
  if ($moviePoster.length) {
    const $posterContainer = $moviePoster.parent();
    $posterContainer.addClass("poster-container");
  }

  // Add to favorites functionality
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
      dataType: "json",
      data: JSON.stringify({
        movie_id: movie.id,
        title: movie.title,
        poster_path: movie.poster_path,
        status: "watchlist",
      }),
      success: function (response) {
        console.log("Server response:", response);
        alert("✅ Film berhasil ditambahkan ke favorit!");
      },
      error: function (xhr, status, error) {
        const responseText = xhr.responseText || error;

        if (xhr.status === 409 || responseText.includes("Duplicate")) {
          alert("⚠️ Film sudah ditambahkan ke favorit!");
        } else {
          console.error("Gagal menambahkan:", responseText);
          alert(`❌ Gagal menambahkan ke favorit: ${responseText}`);
        }
      },
    });
  }
});