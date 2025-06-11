@props(['action', 'tooltip' => 'Delete', 'class' => ''])

<form action="{{ $action }}" method="POST" class="delete-form {{ $class }} d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="{{ $tooltip }}">
        <i class="fas fa-trash-alt mr-1"></i>
    </button>
</form>