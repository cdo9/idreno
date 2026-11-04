// tableSorter.js - SEULEMENT ça
$(document).ready(function() {
    $('.sortable').tablesorter({ usNumberFormat: false });
    
    // Livewire : UPDATE au lieu de destroy
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', () => {
            $('.sortable').trigger('updateAll');
        });
    }
});
