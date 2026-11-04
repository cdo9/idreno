@props(['showModal' => false])

<script>

document.addEventListener('livewire:init', () => {
    Livewire.on('open-relance-modal', (data) => {
        showRelanceModal(data.clientId, data.rendezVousId);
    });
    
    Livewire.on('close-relance-modal', () => {
        Swal.close();
    });
});

function showRelanceModal(clientId, rendezVousId = null) {
    Swal.fire({
        title: 'Marquer comme relancé',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirmer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#77BC23',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusConfirm: false,
        customClass: {
            popup: 'swal-wide',
            confirmButton: 'swal-confirm-btn',
            cancelButton: 'swal-cancel-btn'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            
            @this.call('marquerRelance');
            
            Swal.fire({
                title: 'Succès!',
                text: 'La relance a été marquée avec succès.',
                icon: 'success',
                confirmButtonColor: '#77BC23',
                timer: 2000,
                timerProgressBar: true
            });
        }
    });
}

</script>
