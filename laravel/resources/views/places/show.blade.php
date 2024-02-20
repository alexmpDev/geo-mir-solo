@extends('layouts.box-app')

@section('box-title')
    {{ __('Place') . " " . $place->id }}
@endsection

@section('box-content')
<x-columns columns=2>
    @section('column-1')
        <img class="w-full" src="{{ asset('storage/'.$file->filepath) }}" title="Image preview"/>
    @endsection
    @section('column-2')
        <table class="table">
            <tbody>
                <tr>
                    <td><strong>ID<strong></td>
                    <td>{{ $place->id }}</td>
                </tr>
                <tr>
                    <td><strong>Name</strong></td>
                    <td>{{ $place->name }}</td>
                </tr>
                <tr>
                    <td><strong>Description</strong></td>
                    <td>{{ $place->description }}</td>
                </tr>
                <tr>
                    <td><strong>Lat</strong></td>
                    <td>{{ $place->latitude }}</td>
                </tr>
                <tr>
                    <td><strong>Lng</strong></td>
                    <td>{{ $place->longitude }}</td>
                </tr>
                <tr>
                    <td><strong>Author</strong></td>
                    <td>{{ $author->name }}</td>
                </tr>
                <tr>
                    <td><strong>Visibility</strong></td>
                    <td>{{ $place->visibility->name }}</td>
                </tr>
                <tr>
                    <td><strong>Created</strong></td>
                    <td>{{ $place->created_at }}</td>
                </tr>
                <tr>
                    <td><strong>Updated</strong></td>
                    <td>{{ $place->updated_at }}</td>
                </tr>
            </tbody>
        </table>
        <div class="mt-8">
            @can('update', $place)
            <x-primary-button href="{{ route('places.edit', $place) }}">
                {{ __('Edit') }}
            </x-danger-button>
            @endcan
            @can('delete', $place)
            <x-danger-button href="{{ route('places.delete', $place) }}">
                {{ __('Delete') }}
            </x-danger-button>
            @endcan
            @can('viewAny', App\Models\Place::class)
            <x-secondary-button href="{{ route('places.index') }}">
                {{ __('Back to list') }}
            </x-secondary-button>
            @endcan
        </div>
        <div class="mt-8">
            <p>{{ $numFavs . " " . __('favorites') }}</p>
            @include('partials.buttons-favs')
        </div>
        <form method="POST" action="{{ route('places.review', $place->id) }}">
            @csrf
            <div>
                <x-input-label for="message" :value="__('Message')" />
                <x-textarea name="message" id="message" class="block mt-1 w-full" :value="old('description')" />
            </div>
            <div class="mt-8">
                <x-primary-button>
                    {{ __('Create') }}
                </x-primary-button>
                <x-secondary-button type="reset">
                    {{ __('Reset') }}
                </x-secondary-button>

            </div>
        </form>
        @if ($listMessages != null)
            <div class="bg-gray-100 p-4 rounded-lg">
                @foreach ($listMessages as $message)
                <form action="{{ route('places.review.delete', $message->place_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p class="text-gray-800">{{$message->message}}</p>
                        @if ($message->user_id == auth()->user()->id)
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded mt-2">Eliminar Comentario</button>
                        @endif
                    </div>
                </form>
                @endforeach
            </div>
        @endif
    
    @endsection
</x-columns>
@endsection
