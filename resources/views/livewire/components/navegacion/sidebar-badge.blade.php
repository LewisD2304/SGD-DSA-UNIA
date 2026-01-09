<div class="menu-badge">
    @if($cantidad > 0)
        <span class="badge badge-circle badge-danger w-20px h-20px fs-9"
              data-bs-toggle="tooltip"
              title="{{ $cantidad }} documentos pendientes">
            {{ $cantidad > 99 ? '+99' : $cantidad }}
        </span>
    @endif
</div>
