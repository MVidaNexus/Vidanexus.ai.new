<script>
(function () {
    var stored = localStorage.getItem('theme');
    var theme = stored || 'dark';
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.style.colorScheme = theme === 'dark' ? 'dark' : 'light';
})();
</script>
