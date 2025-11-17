<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">
      <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
      Non-Conformance Reports
    </h1>
    <div>
      <a href="#" class="btn btn-dark btn-sm">
        <i class="fas fa-plus mr-1"></i> NCR
      </a>
    </div>
  </div>
@endsection


@section('content')



<div class="row">


    {{-- PANEL CENTRAL: plano + globos --}}
    <div class="col-md-10">
        <div class="card h-100">
            <div class="card-header p-2">
                <strong>Plano</strong>
                <small class="text-muted ml-2">Clic para crear globo, arrastra para mover</small>
            </div>
            <div class="card-body text-center">
                <div id="wrap" style="position: relative; display:inline-block; border:1px solid #ddd; max-width:100%;">
                    <img id="dwgImg"
                         src="{{ asset('storage/'.$drawing->file_path) }}"
                         style="max-width:100%; display:block;">

                    @foreach($drawing->characteristics as $c)
                        @php
                            $left = $c->x * 100;
                            $top  = $c->y * 100;
                        @endphp
                        <div class="balloon"
                             data-id="{{ $c->id }}"
                             data-rx="{{ $c->x }}"
                             data-ry="{{ $c->y }}"
                             style="left: calc({{ $left }}% - 16px); top: calc({{ $top }}% - 16px);">
                            {{ $c->char_no }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- PANEL DERECHO: formulario de detalle --}}
    <div class="col-md-2">
        <div class="card h-100">
            <div class="card-header p-2">
                <strong>Detalle característica</strong>
            </div>
            <div class="card-body p-2">
                <form id="char-form">
                    <input type="hidden" id="active-char-id">

                    <div class="form-group mb-2">
                        <label class="mb-0">Char. No.</label>
                        <input type="text" class="form-control form-control-sm" id="char-no" disabled>
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">Reference location</label>
                        <input type="text" class="form-control form-control-sm" id="char-ref-loc">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">Characteristic</label>
                        <input type="text" class="form-control form-control-sm" id="char-designator">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">Requirement</label>
                        <input type="text" class="form-control form-control-sm" id="char-requirement">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">Results</label>
                        <input type="text" class="form-control form-control-sm" id="char-results">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">Tooling</label>
                        <input type="text" class="form-control form-control-sm" id="char-tooling">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">NC Number</label>
                        <input type="text" class="form-control form-control-sm" id="char-nc">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-0">Comments</label>
                        <textarea class="form-control form-control-sm" id="char-comments" rows="2"></textarea>
                    </div>

                    <button type="button" id="btn-save-char" class="btn btn-sm btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i>Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>

        {{-- PANEL IZQUIERDO: lista de características --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header p-2">
                <strong>Características</strong>
            </div>
            <div class="card-body p-2" style="max-height: 70vh; overflow:auto;">
                <ul id="char-list" class="list-group list-group-sm">
                    @foreach($drawing->characteristics as $c)
                        <li class="list-group-item py-1 px-2 char-item"
                            data-id="{{ $c->id }}">
                            <span class="badge badge-primary mr-1">{{ $c->char_no }}</span>
                            <small>
                                {{ $c->reference_location ?? 'Sin ref.' }}
                                @if($c->requirement)
                                    – {{ Str::limit($c->requirement, 20) }}
                                @endif
                            </small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>




<!--  {{-- Tab: By End Schedule --}}-->

@endsection



@section('css')
<!-- CSS complementario (puedes ponerlo en tu .css) -->
<style>
    .balloon {
        position:absolute;
        width:32px;
        height:32px;
        border-radius:50%;
        border:2px solid #007bff;
        color:#007bff;
        background:#fff;
        font-weight:bold;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:move;
        user-select:none;
        box-shadow:0 2px 6px rgba(0,0,0,.15);
        font-size:0.85rem;
    }
    .balloon.active {
        background:#007bff;
        color:#fff;
    }
    .char-item.active {
        background:#007bff;
        color:#fff;
    }
</style>
@endsection


@push('js')

<script>
(function () {
    const img   = document.getElementById('dwgImg');
    const wrap  = document.getElementById('wrap');
    const token = '{{ csrf_token() }}';

    // Datos de características para rellenar el panel derecho
const CHAR_DATA = @json($charData);

    const storeUrl = @json(route('qa.drawings.characteristics.store', ['drawing' => $drawing->id]));
    const updateUrlTemplate = @json(
        route('qa.drawings.characteristics.update', ['drawing' => $drawing->id, 'char' => '__ID__'])
    );

    // ========= seleccionar característica =========
    function setActive(id) {
        if (!CHAR_DATA[id]) return;

        // guardar id activo
        document.getElementById('active-char-id').value = id;

        // resaltar lista
        document.querySelectorAll('.char-item').forEach(li => {
            li.classList.toggle('active', li.dataset.id === String(id));
        });

        // resaltar globos
        document.querySelectorAll('.balloon').forEach(b => {
            b.classList.toggle('active', b.dataset.id === String(id));
        });

        const c = CHAR_DATA[id];

        // rellenar formulario
        document.getElementById('char-no').value          = c.char_no ?? '';
        document.getElementById('char-ref-loc').value     = c.reference_location ?? '';
        document.getElementById('char-designator').value  = c.characteristic_designator ?? '';
        document.getElementById('char-requirement').value = c.requirement ?? '';
        document.getElementById('char-results').value     = c.results ?? '';
        document.getElementById('char-tooling').value     = c.tooling ?? '';
        document.getElementById('char-nc').value          = c.non_conformance_number ?? '';
        document.getElementById('char-comments').value    = c.comments ?? '';
    }

    // clic en lista
    document.querySelectorAll('.char-item').forEach(li => {
        li.addEventListener('click', function () {
            setActive(this.dataset.id);
        });
    });

    // clic en globo
    document.querySelectorAll('.balloon').forEach(b => {
        b.addEventListener('click', function (e) {
            // evitar que el clic dispare el 'wrap click' (crear globo)
            e.stopPropagation();
            setActive(this.dataset.id);
        });
    });

    // seleccionar la primera al entrar
    const first = document.querySelector('.char-item');
    if (first) {
        setActive(first.dataset.id);
    }

    // ========= crear nuevo globo al clic en la imagen =========
    wrap.addEventListener('click', function (e) {
        if (e.target !== img) return;

        const rect = img.getBoundingClientRect();
        const xpx  = e.clientX - rect.left;
        const ypx  = e.clientY - rect.top;

        const rx = xpx / img.clientWidth;
        const ry = ypx / img.clientHeight;

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ x: rx, y: ry })
        })
        .then(r => r.json())
        .then(c => {
            // agregar a datos
            CHAR_DATA[c.id] = {
                id: c.id,
                char_no: c.char_no,
                reference_location: null,
                characteristic_designator: null,
                requirement: null,
                results: null,
                tooling: null,
                non_conformance_number: null,
                comments: null,
                x: c.x,
                y: c.y,
            };

            // agregar a lista
            const li = document.createElement('li');
            li.className = 'list-group-item py-1 px-2 char-item';
            li.dataset.id = c.id;
            li.innerHTML = `<span class="badge badge-primary mr-1">${c.char_no}</span><small> Nueva</small>`;
            li.addEventListener('click', function () {
                setActive(this.dataset.id);
            });
            document.getElementById('char-list').appendChild(li);

            // agregar globo
            addBalloon(c.id, c.char_no, c.x, c.y);

            // seleccionar la nueva
            setActive(c.id);
        })
        .catch(err => console.error(err));
    });

    function addBalloon(id, no, rx, ry) {
        const el = document.createElement('div');
        el.className = 'balloon';
        el.dataset.id = id;
        el.dataset.rx = rx;
        el.dataset.ry = ry;
        el.textContent = no;
        position(el, rx, ry);
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            setActive(this.dataset.id);
        });
        wrap.appendChild(el);
        makeDraggable(el);
    }

    function position(el, rx, ry) {
        el.style.left = `calc(${rx * 100}% - 16px)`;
        el.style.top  = `calc(${ry * 100}% - 16px)`;
        el.dataset.rx = rx;
        el.dataset.ry = ry;
    }

    // ========= drag & drop globos (igual que antes, pero usando updateUrlTemplate) =========
    document.querySelectorAll('.balloon').forEach(makeDraggable);

    function makeDraggable(el) {
        let dragging = false;
        let offsetX  = 0;
        let offsetY  = 0;

        el.addEventListener('mousedown', function (e) {
            dragging = true;
            const rect = el.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            e.preventDefault();
        });

        document.addEventListener('mousemove', function (e) {
            if (!dragging) return;

            const imgRect = img.getBoundingClientRect();
            const centerX = e.clientX - imgRect.left - offsetX + el.offsetWidth / 2;
            const centerY = e.clientY - imgRect.top  - offsetY + el.offsetHeight / 2;

            let rx = centerX / img.clientWidth;
            let ry = centerY / img.clientHeight;

            rx = Math.min(0.99, Math.max(0.01, rx));
            ry = Math.min(0.99, Math.max(0.01, ry));

            position(el, rx, ry);
        });

        document.addEventListener('mouseup', function () {
            if (!dragging) return;
            dragging = false;

            const id = el.dataset.id;
            const rx = parseFloat(el.dataset.rx);
            const ry = parseFloat(el.dataset.ry);

            const url = updateUrlTemplate.replace('__ID__', id);

            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ x: rx, y: ry })
            }).catch(err => console.error(err));
        });
    }

    // ========= guardar formulario (detalle) =========
    document.getElementById('btn-save-char').addEventListener('click', function () {
        const id = document.getElementById('active-char-id').value;
        if (!id) return;

        const url = updateUrlTemplate.replace('__ID__', id);

        const payload = {
            reference_location:        document.getElementById('char-ref-loc').value || null,
            characteristic_designator: document.getElementById('char-designator').value || null,
            requirement:               document.getElementById('char-requirement').value || null,
            results:                   document.getElementById('char-results').value || null,
            tooling:                   document.getElementById('char-tooling').value || null,
            non_conformance_number:    document.getElementById('char-nc').value || null,
            comments:                  document.getElementById('char-comments').value || null,
        };

        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(() => {
            // actualizar cache local
            Object.assign(CHAR_DATA[id], payload);

            // actualizar texto en lista
            const li = document.querySelector(`.char-item[data-id="${id}"]`);
            if (li) {
                li.innerHTML = `<span class="badge badge-primary mr-1">${CHAR_DATA[id].char_no}</span>
                    <small>${CHAR_DATA[id].reference_location || 'Sin ref.'}
                    ${CHAR_DATA[id].requirement ? ' – ' + CHAR_DATA[id].requirement.substring(0,20) : ''}</small>`;
            }
        })
        .catch(err => console.error(err));
    });

})();
</script>

@endpush