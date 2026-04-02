@php
    $selected = collect(old('permissions', $selectedIds ?? []))->map(fn ($v) => (int) $v)->all();
    $grouped = $permissions->groupBy(fn ($p) => $p->group ?: 'autres');
@endphp

<div class="space-y-6">
    @foreach ($grouped as $groupKey => $perms)
        <fieldset class="rounded-xl border border-slate-200/80 dark:border-slate-600/80 p-4 sm:p-5 bg-slate-50/50 dark:bg-slate-900/30">
            <legend class="px-2 text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400">
                @if ($groupKey === 'autres')
                    Autres
                @else
                    {{ $groupKey }}
                @endif
            </legend>
            <ul class="mt-3 grid gap-2 sm:grid-cols-1 md:grid-cols-2">
                @foreach ($perms as $perm)
                    <li>
                        <label class="flex items-start gap-2.5 cursor-pointer rounded-lg p-2 -m-2 hover:bg-white/80 dark:hover:bg-slate-800/60 transition-colors">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="mt-1 rounded border-slate-300 dark:border-slate-600 text-[#00b464] focus:ring-[#00b464]/35"
                                @checked(in_array((int) $perm->id, $selected, true))>
                            <span class="text-sm text-slate-800 dark:text-slate-200 leading-snug">
                                <span class="font-medium">{{ $perm->label }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-500 font-mono mt-0.5">{{ $perm->name }}</span>
                            </span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </fieldset>
    @endforeach
</div>
