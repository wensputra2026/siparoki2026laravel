function formatDateLabel(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return days[date.getDay()] + ', ' + date.getDate() + ' ' + months[date.getMonth() + 1] + ' ' + date.getFullYear();
}
function scrollToDate(dateStr) {
    const cards = document.querySelectorAll('.schedule-card');
    const days = document.querySelectorAll('.jm-calendar-day');
    const indicator = document.getElementById('filterIndicator');
    const display = document.getElementById('filterDateDisplay');
    const target = document.getElementById('jadwal-' + dateStr);
    days.forEach(day => day.classList.remove('active-date'));
    document.querySelectorAll('[data-date="' + dateStr + '"]').forEach(day => day.classList.add('active-date'));
    cards.forEach(card => card.style.display = 'none');
    if (target) {
        target.style.display = 'block';
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    if (display) display.textContent = formatDateLabel(dateStr);
    if (indicator) indicator.classList.remove('hidden');
}
function showAllDates() {
    document.querySelectorAll('.schedule-card').forEach(card => card.style.display = 'block');
    document.querySelectorAll('.jm-calendar-day').forEach(day => day.classList.remove('active-date'));
    const indicator = document.getElementById('filterIndicator');
    if (indicator) indicator.classList.add('hidden');
    document.getElementById('jadwalContainer')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
