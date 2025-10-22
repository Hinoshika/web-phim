document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('animeList');
  const errorMessage = document.getElementById('errorMessage');

  fetch('/api/anime/get_all.php')
    .then(res => {
      if (!res.ok) throw new Error('Không thể lấy dữ liệu từ máy chủ');
      return res.json();
    })
    .then(data => {
      const animes = Array.isArray(data) ? data : data.items;

      if (!Array.isArray(animes)) {
        throw new Error("Dữ liệu trả về không hợp lệ");
      }

      container.innerHTML = '';

      animes.forEach(anime => {
        const card = document.createElement('div');
        card.className = 'anime-card';

        card.innerHTML = `
          <img src="${anime.anh_bia}" alt="${anime.tieu_de}">
          <h3>${anime.tieu_de}</h3>
          <p>Thể loại: ${anime.the_loai}</p>
          <p>Điểm: ${anime.diem_trung_binh}</p>
          <div class="card-actions">
            <button class="edit-btn">Sửa</button>
            <button class="delete-btn">Xoá</button>
          </div>
        `;

      
        card.querySelector('.edit-btn').addEventListener('click', () => {
          window.location.href = `edit_anime.html?id=${anime.id}`;
        });

      
        card.querySelector('.delete-btn').addEventListener('click', () => {
          deleteAnime(anime.id);
        });

        container.appendChild(card);
      });
    })
    .catch(err => {
      errorMessage.textContent = 'Lỗi khi tải dữ liệu: ' + err.message;
      console.error(err);
    });
});

function deleteAnime(id) {
  if (!confirm('Bạn có chắc muốn xoá anime này?')) return;

  fetch(`/api/anime/delete_a.php?id=${id}`, {
    method: 'DELETE',
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Đã xoá thành công');
        location.reload();
      } else {
        alert(data.message || 'Lỗi khi xoá');
      }
    })
    .catch(err => {
      alert('Lỗi server: ' + err.message);
    });
}
