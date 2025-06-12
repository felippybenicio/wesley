fetch('get_id.php')
  .then(res => res.json())
  .then(data => {
    if (data.id) {
      document.getElementById('id').value = data.id;
    }
  });
