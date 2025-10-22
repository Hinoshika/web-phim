document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('profileForm');
  const message = document.getElementById('message');

  fetch('/api/users/get_current_user.php')
    .then(res => res.json())
    .then(user => {
      if (user.error) {
        message.textContent = 'Chưa đăng nhập!';
        form.style.display = 'none';
        return;
      }

      form.id.value = user.id;
      form.username.value = user.username;
      form.name.value = user.name || '';
      form.email.value = user.email || '';
      form.loai_tk.value = user.loai_tk;
      form.avatar.value = user.avatar || '';
    });

  form.addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/api/users/update_profile.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(result => {
      if (result.success) {
        message.style.color = 'green';
        message.textContent = 'Cập nhật thành công!';
      } else {
        message.style.color = 'red';
        message.textContent = 'Lỗi: ' + result.message;
      }
    });
  });
});
