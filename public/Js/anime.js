
const params = new URLSearchParams(window.location.search);
const id = params.get('id');

fetch(`/api/anime/get_anime.php?id=${id}`)
  .then(res => {
    if (!res.ok) throw new Error('Lỗi khi lấy dữ liệu từ máy chủ');
    return res.json();
  })
  .then(anime => {
    const detailContainer = document.getElementById('anime-detail');

    if (anime.error) {
      detailContainer.innerHTML = `<p>${anime.error}</p>`;
      return;
    }

  
    document.body.insertAdjacentHTML('afterbegin', `
      <div class="background-image" style="background-image: url('${anime.anh_bia}')"></div>
    `);

    // Hiển thị chi tiết anime
    detailContainer.innerHTML = `
      <h1>${anime.tieu_de}</h1>
      <img src="${anime.anh_bia}" style="max-width: 200px;" />
      <p>Studio: ${anime.studio}</p>
      <p>Thể loại: ${anime.the_loai}</p>
      <p>Điểm: <span id="diem">${anime.diem_trung_binh ?? 0}%</span></p>
      <p>Trạng thái: ${anime.trang_thai}</p>
      <p>Tóm tắt: ${anime.tom_tat}</p>

      <button id="likeBtn">❤️ Yêu thích</button>

      <div id="ratingSection">
        <label for="rating">Đánh giá của bạn:</label>
        <select id="rating">
          ${[...Array(10)].map((_, i) => {
            const val = 10 - i;
            return `<option value="${val}">${val} ${val === 10 ? '- Tuyệt vời' : val === 5 ? '- Trung bình' : ''}</option>`;
          }).join('')}
        </select>
        <button id="submitRating">Gửi đánh giá</button>
      </div>

      <h3>Danh sách tập:</h3>
      <div class="episode-list">
        ${(anime.episodes ?? []).map(tap => `
          <a href="${tap.link}" target="_blank">Tập ${tap.so_tap}</a>
        `).join('')}
      </div>
    `;

   
          let isLiked = false;

      fetch('/api/anime/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ anime_id: id })
      })
        .then(res => res.json())
        .then(data => {
          isLiked = data.liked;
          document.getElementById('likeBtn').textContent = isLiked ? '💔 Bỏ thích' : '❤️ Yêu thích';
        });

    
      document.getElementById('likeBtn').addEventListener('click', () => {
        fetch('/api/anime/like.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ anime_id: id })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              isLiked = data.liked;
              document.getElementById('likeBtn').textContent = isLiked ? '💔 Bỏ thích' : '❤️ Yêu thích';
              alert(data.message);
            } else {
              alert(data.message);
            }
          })
          .catch(() => alert('Lỗi khi gửi yêu cầu'));
      });

      document.getElementById('submitRating').addEventListener('click', () => {
  const rating = parseInt(document.getElementById('rating').value);

  fetch('/api/anime/rate.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ anime_id: id, rating })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('🎉 Cảm ơn bạn đã đánh giá!');
        const newScore = data.new_average || 0;
        document.getElementById('diem').textContent = `${newScore}%`;

        document.getElementById('submitRating').disabled = true;
        document.getElementById('rating').disabled = true;
      } else {
        alert(data.message || '⚠️ Bạn cần đăng nhập để đánh giá.');
      }
    })
    .catch(err => {
      alert('Lỗi khi gửi đánh giá: ' + err.message);
    });
});


  })
  .catch(err => {
    document.getElementById('anime-detail').innerHTML = `<p style="color: red;">Lỗi: ${err.message}</p>`;
    console.error(err);
  });
