// Fungsi untuk update favorite
function updateFavorite(id, data) {
  $.ajax({
    url: "backend/favorites.php",
    method: "PUT",
    data: $.param({ id, ...data }),
    success: () => loadFavorites(),
    error: function (xhr, status, error) {
      console.error("Error updating favorite:", error);
      alert("Gagal memperbarui data film favorit");
    }
  });
}

// Fungsi untuk delete favorite
function deleteFavorite(id) {
  $.ajax({
    url: "backend/favorites.php",
    method: "DELETE",
    data: $.param({ id }),
    success: function () {
      location.reload();
    },
    error: function (xhr, status, error) {
      console.error("Error deleting favorite:", error);
      alert("Gagal menghapus film favorit");
    }
  });
}

// Fungsi untuk reset poster
function resetPoster(id) {
  $.ajax({
    url: "backend/reset_poster.php",
    method: "POST",
    data: { id: id },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        alert("Poster berhasil direset ke default!");
        location.reload();
      } else {
        alert(response.message || "Terjadi kesalahan saat mereset poster");
      }
    },
    error: function (xhr, status, error) {
      console.error("Reset poster error:", error);
      alert("Gagal mereset poster: " + error);
    },
  });
}

function checkLoginStatus() {
    $.ajax({
        url: 'backend/check_session.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const navLinks = $('#nav-links'); // Target elemen nav
            if (response.loggedIn) {
                // Jika sudah login
                navLinks.html(`
                    <li><a href="#sinopsis">Sinopsis</a></li>
                    <li><a href="#details">Detail Film</a></li>
                    <li><a href="#" id="cast-link">Pemeran</a></li>
                    <li><a href="favorites.html">Favorites (${response.username})</a></li>
                    <li><a href="backend/logout.php" class="text-red-500">Logout</a></li>
                `);
            } else {
                // Jika belum login
                navLinks.html(`
                    <li><a href="#sinopsis">Sinopsis</a></li>
                    <li><a href="#details">Detail Film</a></li>
                    <li><a href="#" id="cast-link">Pemeran</a></li>
                    <li><a href="auth.html">Login</a></li>
                `);
                // Jika ini halaman favorites, redirect ke login
                if (window.location.pathname.includes('favorites.html')) {
                    window.location.href = 'auth.html';
                }
            }
        }
    });
}

$(document).ready(function () {
  const $darkModeToggle = $("#toggle-dark-mode");
  const $body = $("body");

  checkLoginStatus();

  if ($("#favorites-list").length) {
    loadFavorites();
  }

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

  function loadFavorites() {
    $.getJSON("backend/favorites.php", function (data) {
      const $list = $("#favorites-list");
      $list.empty();

      if (data.length === 0) {
        $list.html(`
          <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="far fa-sad-tear"></i>
            <h3>Belum ada film favorit</h3>
            <p>Cari dan tambahkan film ke koleksi favorit Anda untuk melihatnya di sini</p>
          </div>
        `);
        return;
      }

      data.forEach((movie) => {
        const hasCustomPoster = movie.custom_poster ? true : false;
        const posterUrl = movie.custom_poster
          ? movie.custom_poster
          : movie.poster_path
          ? `https://image.tmdb.org/t/p/w500${movie.poster_path}`
          : "https://via.placeholder.com/500x750?text=No+Poster";

        let starsHtml = "";
        const rating = parseInt(movie.rating) || 0;

        for (let i = 1; i <= 5; i++) {
          starsHtml += `<i class="star fas fa-star${
            i <= rating ? " active" : ""
          }" data-value="${i}"></i>`;
        }

        const card = `
          <div class="movie-card" data-id="${movie.id}" data-movie-id="${
          movie.movie_id
        }">
            <div class="movie-poster-container">
              <img src="${posterUrl}" alt="${movie.title}" class="movie-poster">
             </div>
            <div class="movie-info">
              <h3 class="movie-title">${movie.title}</h3> 
              <div class="rating-container">
                <div class="star-rating" data-id="${movie.id}">
                  ${starsHtml}
                </div>
              </div>

              <div class="action-buttons">
                <button class="action-button watched ${
                  movie.status === "watched" ? "active" : ""
                }" data-id="${movie.id}" data-type="watched">
                  <i class="fas fa-check-circle"></i>
                </button>
                <button class="action-button ${
                  movie.loved == 1 ? "active" : ""
                }" data-id="${movie.id}" data-type="love">
                  <i class="fas fa-heart"></i>
                </button>
                <button class="action-button watchlist ${
                  movie.status === "watchlist" ? "active" : ""
                }" data-id="${movie.id}" data-type="watchlist">
                  <i class="fas fa-bookmark"></i>
                </button>
              </div>

              <button class="delete-button" data-id="${movie.id}">
                <i class="fas fa-trash-alt"></i> Hapus
              </button>
              <input type="file" accept="image/*" class="poster-input" data-film-id="${
                movie.id
              }" style="display: none;">
              ${
                hasCustomPoster
                  ? `<button class="reset-poster-btn" data-id="${movie.id}" style="display: block; margin-top: 10px;">Reset ke poster default</button>`
                  : `<button class="change-poster-btn" style="display: block; margin-top: 10px;">Ganti dengan custom poster</button>`
              }
            </div>
          </div>
        `;

        $list.append(card);
      });

      $(".star").on("click", function () {
        const $star = $(this);
        const value = $star.data("value");
        const $card = $star.closest(".movie-card");
        const id = $card.data("id");

        $card.find(".star").each(function () {
          const $this = $(this);
          if ($this.data("value") <= value) {
            $this.addClass("active");
          } else {
            $this.removeClass("active");
          }
        });

        updateFavorite(id, {
          rating: value,
        });
      });

      $(".action-button").on("click", function () {
        const $button = $(this);
        const id = $button.data("id");
        const type = $button.data("type");

        if (type === "love") {
          const isLoved = $button.hasClass("active");
          $button.toggleClass("active");
          updateFavorite(id, {
            loved: isLoved ? 0 : 1,
          });
        } else {
          $button
            .closest(".action-buttons")
            .find(".action-button")
            .removeClass("active");
          $button.addClass("active");

          updateFavorite(id, {
            status: type,
          });
        }
      });

      $(".delete-button").on("click", function () {
        const $button = $(this);
        const id = $button.data("id");
        const title = $button
          .closest(".movie-card")
          .find(".movie-title")
          .text();

        if (
          confirm(
            `Apakah Anda yakin ingin menghapus "${title}" dari daftar favorit?`
          )
        ) {
          deleteFavorite(id);
        }
      });

      $(".change-poster-btn").on("click", function () {
        const $input = $(this).siblings(".poster-input");
        const movieId = $(this).closest(".movie-card").data("id");
        $input.data("film-id", movieId);
        $input.click();
      });
      
      $(".reset-poster-btn").on("click", function () {
        const id = $(this).data("id");
        if (
          confirm("Apakah Anda yakin ingin mengembalikan poster ke default?")
        ) {
          resetPoster(id);
        }
      });

      $(".poster-input").on("change", function () {
        const file = this.files[0];
        const filmId = $(this).data("film-id");

        if (!file) {
          return;
        }

        if (!filmId) {
          console.error("Missing film ID");
          alert("Error: Film ID not found");
          return;
        }

        const formData = new FormData();
        formData.append("poster", file);
        formData.append("film_id", filmId);

        $.ajax({
          url: "backend/upload_poster.php",
          method: "POST",
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            try {
              const result =
                typeof response === "string" ? JSON.parse(response) : response;

              if (result.success) {
                alert("Poster berhasil diunggah!");
                location.reload();
              } else {
                alert(
                  result.message || "Terjadi kesalahan saat mengunggah poster"
                );
                console.error("Upload error:", result);
              }
            } catch (e) {
              console.error("Error parsing response:", e, response);
              alert("Terjadi kesalahan saat memproses respons server");
            }
          },
          error: function (xhr, status, error) {
            console.error("Ajax error:", error, xhr.responseText);
            alert("Gagal menghubungi server: " + error);
          },
        });
      });
    });
  }
});