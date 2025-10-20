<div class="flex rounded-md relative">
    <div class="flex">
        <div class="px-2 py-3">
            <div class="h-10 w-10">
                <img src="{{ url('/storage/public/avatars/'.$image.'') }}" alt="{{ $nome }}" role="img" class="h-full w-full rounded-full overflow-hidden shadow object-cover" />
            </div>
        </div>
 
        <div class="flex flex-col justify-center pl-3 py-2">
            <p class="text-sm font-bold pb-1">{{ $nome }}</p>
            <div class="flex flex-col items-start">
                <p class="text-xs leading-5">{{ $email }}</p>
            </div>
        </div>
    </div>
</div>