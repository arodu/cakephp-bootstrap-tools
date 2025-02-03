<?php

/**
 * @var \App\View\AppView $this
 */

$target ??= 'ajax-modal';
$eventJs ??= 'modalResponse';

?>
<div class="modal fade" id="<?= $target ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cargando...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Cargando contenido...</p>
            </div>
        </div>
    </div>
</div>

<script>
    <?= $this->Html->scriptStart(['block' => true]) ?>
    document.addEventListener("DOMContentLoaded", function() {
        const eventJs = "<?= $eventJs ?>";
        const target = "<?= $target ?>";
        const jsCallbackResponse = <?= $jsCallback ?? 'null;' ?>

        const modal = document.getElementById(target);

        async function loadModalContent(url) {
            let modalTitle = modal.querySelector(".modal-title");
            let modalBody = modal.querySelector(".modal-body");

            try {
                let response = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });
                let data = await response.json();

                modalTitle.innerHTML = data.title || "Formulario";
                modalBody.innerHTML = data.html || "<p>Error al cargar contenido</p>";

                let form = modalBody.querySelector("form");
                if (form) {
                    form.addEventListener("submit", handleFormSubmit);
                }
            } catch (error) {
                console.error("Error al cargar el modal:", error);
                modalTitle.innerHTML = "Error";
                modalBody.innerHTML = "<p>No se pudo cargar el contenido.</p>";
            }
        }

        async function handleFormSubmit(event) {
            event.preventDefault();
            let form = event.target;
            let formData = new FormData(form);

            try {
                let response = await fetch(form.action, {
                    method: form.method || "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json",
                        //"X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                let result = await response.json();
                document.dispatchEvent(new CustomEvent(eventJs, {
                    detail: {
                        data: result,
                        modal: modal,
                    }
                }));

            } catch (error) {
                console.error("Error al enviar el formulario:", error);
            }
        }

        modal.addEventListener("show.bs.modal", function(event) {
            let button = event.relatedTarget;
            let url = button.getAttribute("data-url");
            loadModalContent(url);
        });

        document.addEventListener(eventJs, function(event) {
            let {
                data,
                modal
            } = event.detail;

            if (typeof jsCallbackResponse === "function") {
                jsCallbackResponse(event, data, modal);
            }
        });

    });
    <?= $this->Html->scriptEnd() ?>
</script>