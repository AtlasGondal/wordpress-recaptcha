document.addEventListener('DOMContentLoaded', function() {
    const versionSelect = document.getElementById('wp_recaptcha_version');
    const thresholdRow = document.getElementById('wp_recaptcha_v3_threshold').closest('tr');

    function toggleThresholdVisibility() {
        if (versionSelect.value === 'v3') {
            thresholdRow.style.display = '';
        } else {
            thresholdRow.style.display = 'none';
        }
    }

    versionSelect.addEventListener('change', toggleThresholdVisibility);
    toggleThresholdVisibility();
});
