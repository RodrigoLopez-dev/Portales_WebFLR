document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.kanban-card');
    const zones = document.querySelectorAll('.dropzone');
    const modalEl = document.getElementById('kanbanStatusModal');

    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const requestInput = document.getElementById('kanban_request_id');
    const statusInput = document.getElementById('kanban_estado_id');

    let draggedId = null;

    cards.forEach(card => {
        card.addEventListener('dragstart', function () {
            draggedId = this.dataset.requestId;
            this.classList.add('dragging');
        });

        card.addEventListener('dragend', function () {
            this.classList.remove('dragging');
        });
    });

    zones.forEach(zone => {
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('dropzone-over');
        });

        zone.addEventListener('dragleave', function () {
            this.classList.remove('dropzone-over');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('dropzone-over');

            const statusId = this.dataset.statusId;
            if (!draggedId || !statusId) return;

            requestInput.value = draggedId;
            statusInput.value = statusId;
            modal.show();
        });
    });
});