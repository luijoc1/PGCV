<!-- Keep the original table from flashing before pagination is initialized. -->
<style>
html.pgcv-tables-pending #example1,
html.pgcv-tables-pending #example2 { display: none; }
</style>
<script>
document.documentElement.classList.add('pgcv-tables-pending');
// If scripts fail to load, leave the ordinary table available to the user.
setTimeout(function () {
  document.documentElement.classList.remove('pgcv-tables-pending');
}, 8000);
</script>
