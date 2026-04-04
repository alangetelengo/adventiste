@extends('layouts.app')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('page-title', 'Modifier le type de recette')

@section('content')
    @include('parametres._nav')

    <div class="max-w-xl adventiste-card-pro-static p-6 sm:p-8">
        <form method="post" action="{{ route('parametres.types-recette.update', $type) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Code</label>
                <input type="text" name="code" id="code" value="{{ old('code', $type->code) }}" required pattern="[a-z0-9_]+"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-mono">
                @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="libelle" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Libellé</label>
                <input type="text" name="libelle" id="libelle" value="{{ old('libelle', $type->libelle) }}" required maxlength="255"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                @error('libelle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="categorie" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Catégorie</label>
                <select name="categorie" id="categorie" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                    @foreach ($categories as $val => $lib)
                        <option value="{{ $val }}" @selected(old('categorie', $type->categorie) === $val)>{{ $lib }}</option>
                    @endforeach
                </select>
                @error('categorie')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ordre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ordre</label>
                <input type="number" name="ordre" id="ordre" value="{{ old('ordre', $type->ordre) }}" required min="0" max="65535"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                @error('ordre')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="actif" value="1" class="rounded border-slate-300" @checked(old('actif', $type->actif))>
                Actif
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="exclure_rapport_mission" value="1" class="rounded border-slate-300" @checked(old('exclure_rapport_mission', $type->exclure_rapport_mission))>
                Exclure du rapport mission (ex: offrande construction)
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="mission_sans_partage" value="1" class="rounded border-slate-300" @checked(old('mission_sans_partage', $type->mission_sans_partage))>
                Envoyer à la mission sans partage (100%)
            </label>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-emerald-700 text-white px-5 py-2.5 text-sm font-medium hover:bg-emerald-800">Enregistrer</button>
                <a href="{{ route('parametres.types-recette.index') }}" class="rounded-lg border border-slate-300 dark:border-slate-600 px-5 py-2.5 text-sm no-underline inline-flex items-center text-slate-700 dark:text-slate-300">Retour</a>
            </div>
        </form>
    </div>
@endsection
