@if(request()->query('vista_fotografias') === '1' && auth()->user()?->can('administrar'))
    <div data-editor-fotos-vista class="vista-fotos-banda">Vista de administrador <span>Selecciona una imagen para editarla. Los cambios se aplican al guardar.</span></div>
@endif
