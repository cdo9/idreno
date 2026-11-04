<script>

document.addEventListener('livewire:init', () => {
    Livewire.on('relance-success', (event) => {
        console.log('Relance réussie:', event.message);
        Swal.fire({
            icon: 'success',
            title: 'Succès!',
            text: event.message,
            timer: 3000,
            showConfirmButton: false
        });
    });
    
    Livewire.on('relance-error', (event) => {
        console.error('Erreur de relance:', event);
        Swal.fire({
            icon: 'error',
            title: 'Erreur!',
            text: event.message,
            confirmButtonText: 'OK'
        });
    });
});
</script>
