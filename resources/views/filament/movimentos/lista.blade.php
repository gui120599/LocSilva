<!-- resources/views/filament/movimentos/lista.blade.php -->

<div class="space-y-2">
    @foreach($movimentos as $movimento)
        <div class="p-3 border rounded-lg flex justify-between items-center">
            <div>
                <p class="font-bold">{{ $movimento->descricao }}</p>
                <p class="text-sm text-gray-500">R$ {{ number_format($movimento->valor, 2, ',', '.') }}</p>
            </div>
        </div>
    @endforeach
</div>
