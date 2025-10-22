document.addEventListener('DOMContentLoaded', () => {
  const loginBtn = document.getElementById('loginBtn');
  const loginModal = document.getElementById('loginModal');
  const registerModal = document.getElementById('registerModal');
  const closeLogin = document.getElementById('closeLogin');
  const closeRegister = document.getElementById('closeRegister');
  const goToRegister = document.getElementById('goToRegister');
  const goToLogin = document.getElementById('goToLogin');

  const userAvatar = document.getElementById('userAvatar');
  const usernameDisplay = document.getElementById('usernameDisplay');

  let loggedInUser = null;

  function updateUIAfterLogin() {
    const adminPanelBtn = document.getElementById('adminPanelBtn');
        if (userAvatar) {
  userAvatar.addEventListener('click', () => {
    window.location.href = '/profile.html';
           });
      }

    

    if (loggedInUser) {
      loginBtn.style.display = 'none';
      userAvatar.style.display = 'flex';
      usernameDisplay.textContent = loggedInUser.username || 'Người dùng';

      if (loggedInUser.avatar) {
        userAvatar.querySelector('img').src = loggedInUser.avatar;
      }

      if (loggedInUser.role === 'admin') {
        adminPanelBtn.style.display = 'inline-block';
      } else {
        adminPanelBtn.style.display = 'none';
      }
    } else {
      loginBtn.style.display = 'inline-block';
      userAvatar.style.display = 'none';
      adminPanelBtn.style.display = 'none';
    }
  }

  if (loginBtn) {
    loginBtn.addEventListener('click', () => {
      loginModal.style.display = 'flex';
    });
  }

  if (closeLogin) {
    closeLogin.addEventListener('click', () => {
      loginModal.style.display = 'none';
    });
  }

  if (closeRegister) {
    closeRegister.addEventListener('click', () => {
      registerModal.style.display = 'none';
    });
  }

  if (goToRegister) {
    goToRegister.addEventListener('click', (e) => {
      e.preventDefault();
      loginModal.style.display = 'none';
      registerModal.style.display = 'flex';
    });
  }

  if (goToLogin) {
    goToLogin.addEventListener('click', (e) => {
      e.preventDefault();
      registerModal.style.display = 'none';
      loginModal.style.display = 'flex';
    });
  }

  window.addEventListener('click', (e) => {
    if (e.target === loginModal) loginModal.style.display = 'none';
    if (e.target === registerModal) registerModal.style.display = 'none';
  });

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', e => {
      e.preventDefault();

      const username = loginForm.username.value.trim();
      const password = loginForm.password.value;

      fetch('/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            loggedInUser = {
            username: data.username,
            avatar: data.avatar || '/images/default-avatar.png',
            role: data.loai_tk || 'user' 
          };

            updateUIAfterLogin();
            loginModal.style.display = 'none';
            loginForm.reset();
            document.getElementById('loginError').textContent = '';
          } else {
            document.getElementById('loginError').textContent = data.message || 'Đăng nhập thất bại';
          }
        })
        .catch(() => {
          document.getElementById('loginError').textContent = 'Lỗi kết nối server';
        });
    });
  }

  updateUIAfterLogin();

  // Dropdown thể loại
  const dropdown = document.querySelector('.dropdown');
  const dropdownMenu = dropdown?.querySelector('.dropdown-menu');

  if (dropdown && dropdownMenu) {
    dropdown.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdownMenu.style.display = dropdownMenu.style.display === 'flex' ? 'none' : 'flex';
    });

    document.addEventListener('click', function () {
      dropdownMenu.style.display = 'none';
    });
  }

  // Tìm kiếm
  const searchForm = document.getElementById('searchForm');
  if (searchForm) {
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      loadAnime(1);
    });
  }

  // Tải danh sách anime
  loadAnime(1);
});

function loadAnime(page = 1) {
  const search = document.getElementById('search')?.value || '';
  const genre = document.getElementById('the_loai')?.value || '';
  const limit = 20;

  const url = `/api/anime/get_all.php?page=${page}&limit=${limit}&search=${encodeURIComponent(search)}&the_loai=${encodeURIComponent(genre)}`;

  fetch(url)
    .then(res => res.json())
    .then(data => {
      if (!data || !Array.isArray(data.items)) {
        throw new Error("Dữ liệu trả về không hợp lệ");
      }
      renderPage(data.items);
      renderPagination(data.total, page, limit);
    })
    .catch(err => {
      document.getElementById('anime-container').innerHTML = `<p style="color:red;">Lỗi tải dữ liệu: ${err.message}</p>`;
    });
}

function renderPage(items) {
  const container = document.getElementById('anime-container');
  container.innerHTML = '';

  if (!items.length) {
    container.innerHTML = '<p style="color:white;">Không tìm thấy anime phù hợp.</p>';
    return;
  }

  items.forEach(anime => {
    let genres = [];

    try {
      genres = typeof anime.genres === 'string'
        ? JSON.parse(anime.genres)
        : Array.isArray(anime.genres)
          ? anime.genres
          : [];
    } catch {
      genres = [];
    }

    const card = document.createElement('a');
    card.href = `anime.html?id=${anime.id}`;
    card.className = 'anime-card';
    card.innerHTML = `
      <img src="${anime.anh_bia}" alt="${anime.tieu_de}">
      <div class="hover-info">
        <strong>${anime.trang_thai}</strong><br>
        ${anime.so_tap} tập<br>
        ${genres.map(g => `<span class="tag">${g}</span>`).join('')}
        <div class="mt-2">✅ ${anime.diem_trung_binh}%</div>
      </div>
      <div class="title">${anime.tieu_de}</div>
    `;
    container.appendChild(card);
  });
}

function renderPagination(totalItems, currentPage, limit) {
  const pagination = document.getElementById('pagination');
  pagination.innerHTML = '';

  const totalPages = Math.ceil(totalItems / limit);

  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
    btn.addEventListener('click', () => loadAnime(i));
    pagination.appendChild(btn);
  }
}

const adminPanelBtn = document.getElementById('adminPanelBtn');
if (adminPanelBtn) {
  adminPanelBtn.addEventListener('click', () => {
    window.location.href = '/admin.html';
  });
}
document.getElementById("registerForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("reg_username").value;
  const email = document.getElementById("reg_Email").value;
  const password = document.getElementById("reg_password").value;
  const password1 = document.getElementById("reg_password1").value;

  if (password !== password1) {
    alert("Mật khẩu không khớp");
    return;
  }

  fetch("/api/auth/register.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      username,
      password,
      email,
      name: username // hoặc bạn có thể có thêm input tên
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.href = "/index.html";
      } else {
        alert(data.message);
      }
    })
    .catch(err => {
      console.error("Đăng ký lỗi:", err);
    });
});

