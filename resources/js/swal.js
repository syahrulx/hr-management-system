import BaseSwal from 'sweetalert2';

// App-wide dark theme for every popup — matches the black/red glass aesthetic
// used across the dashboard, sidebar, and cards. Import Swal from this file
// (not directly from 'sweetalert2') anywhere a popup is fired, so the theme
// is applied consistently with zero per-call setup.
const Swal = BaseSwal.mixin({
    background: '#110606',
    color: '#f3f4f6',
    confirmButtonColor: '#991b1b',
    cancelButtonColor: 'rgba(255,255,255,0.08)',
    customClass: {
        popup: '!rounded-2xl !border !border-white/10 !shadow-2xl !shadow-black/60',
        title: '!text-white !text-lg !font-bold',
        htmlContainer: '!text-gray-400 !text-sm',
        confirmButton: '!rounded-xl !font-bold !text-sm !px-5 !py-2',
        cancelButton: '!rounded-xl !font-bold !text-sm !px-5 !py-2 !text-gray-300',
    },
});

export default Swal;
