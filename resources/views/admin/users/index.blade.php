@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'Platform User & Administrator Accounts')

@section('content')
<div x-data="{
    createModal: false,
    editModal: false,
    deleteModal: false,
    editUser: { id: null, name: '', email: '', role: 'user' },
    deleteUser: { id: null, name: '' },
    openEdit(user) {
        this.editUser = { ...user };
        this.editModal = true;
    },
    openDelete(user) {
        this.deleteUser = { ...user };
        this.deleteModal = true;
    }
}" class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
        <!-- Role Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-900/90 border border-slate-800">
            <a href="{{ route('admin.users.index') }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ empty($role) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                All ({{ $roleCounts['all'] }})
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $role === 'admin' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                Admins ({{ $roleCounts['admin'] }})
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'editor']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $role === 'editor' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                Editors ({{ $roleCounts['editor'] }})
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'user']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $role === 'user' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                Users ({{ $roleCounts['user'] }})
            </a>
        </div>

        <!-- Search & Add User Button -->
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                @if($role)
                    <input type="hidden" name="role" value="{{ $role }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..."
                           class="pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none w-56">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <button type="submit" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors">
                    Search
                </button>
            </form>

            <button @click="createModal = true" 
                    type="button" 
                    class="py-2 px-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition-all duration-200 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="glass-card rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 border-b border-slate-800">
                        <th class="py-3.5 px-5 font-semibold">User</th>
                        <th class="py-3.5 px-5 font-semibold">Email Address</th>
                        <th class="py-3.5 px-5 font-semibold">Role</th>
                        <th class="py-3.5 px-5 font-semibold">Created Date</th>
                        <th class="py-3.5 px-5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-900/40 transition-colors group">
                            <!-- Name & Avatar -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr {{ $user->role === 'admin' ? 'from-indigo-600 to-violet-600' : ($user->role === 'editor' ? 'from-sky-600 to-indigo-600' : 'from-slate-700 to-slate-800') }} flex items-center justify-center font-bold text-white text-xs shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm flex items-center gap-2">
                                            <span>{{ $user->name }}</span>
                                            @if($user->id === Auth::id())
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-950 border border-indigo-500/40 text-indigo-300">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="py-3.5 px-5 font-mono text-slate-300">
                                {{ $user->email }}
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-5">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-950/80 text-purple-300 border border-purple-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                                        Administrator
                                    </span>
                                @elseif($user->role === 'editor')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-950/80 text-sky-300 border border-sky-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                        Editor
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-900 text-slate-300 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                        Standard User
                                    </span>
                                @endif
                            </td>

                            <!-- Created Date -->
                            <td class="py-3.5 px-5 text-slate-400">
                                {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}
                                <span class="text-[10px] text-slate-500 block">{{ $user->created_at?->diffForHumans() }}</span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button @click="openEdit({
                                                id: {{ $user->id }},
                                                name: '{{ addslashes($user->name) }}',
                                                email: '{{ addslashes($user->email) }}',
                                                role: '{{ $user->role }}'
                                            })"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-medium text-xs transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Delete Button (Self protected) -->
                                    @if($user->id !== Auth::id())
                                        <button @click="openDelete({
                                                    id: {{ $user->id }},
                                                    name: '{{ addslashes($user->name) }}'
                                                })"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-500/20 font-medium text-xs transition-colors flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            <span>Delete</span>
                                        </button>
                                    @else
                                        <span class="px-2.5 py-1.5 text-xs text-slate-600 cursor-not-allowed" title="Self-protection active">Protected</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                No users found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-900/40">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div x-show="createModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity" @click="createModal = false"></div>

            <div class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform glass-card rounded-2xl shadow-2xl border border-slate-700/80">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Create New User</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 pt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name</label>
                        <input type="text" name="name" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                               placeholder="e.g. John Doe">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                        <input type="email" name="email" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                               placeholder="user@example.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">System Role</label>
                        <select name="role" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="user">Standard User</option>
                            <option value="editor">Content Editor</option>
                            <option value="admin">Administrator (Full Access)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Initial Password</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                               placeholder="Minimum 8 characters">
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex justify-end gap-2">
                        <button @click="createModal = false" type="button" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition-colors shadow-md shadow-indigo-600/30">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity" @click="editModal = false"></div>

            <div class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform glass-card rounded-2xl shadow-2xl border border-slate-700/80">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Edit User Account</h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/admin/users/' + editUser.id" method="POST" class="space-y-4 pt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name</label>
                        <input type="text" name="name" x-model="editUser.name" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editUser.email" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">System Role</label>
                        <select name="role" x-model="editUser.role" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="user">Standard User</option>
                            <option value="editor">Content Editor</option>
                            <option value="admin">Administrator (Full Access)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Reset Password <span class="text-slate-500 font-normal">(Leave blank to keep current)</span></label>
                        <input type="password" name="password" minlength="8"
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                               placeholder="New password (optional)">
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex justify-end gap-2">
                        <button @click="editModal = false" type="button" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white transition-colors shadow-md shadow-indigo-600/30">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div x-show="deleteModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity" @click="deleteModal = false"></div>

            <div class="relative inline-block w-full max-w-sm p-6 my-8 overflow-hidden text-left align-middle transition-all transform glass-card rounded-2xl shadow-2xl border border-rose-500/30">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </div>

                <div class="text-center">
                    <h3 class="text-base font-bold text-white" id="delete-modal-title">Delete User Account</h3>
                    <p class="text-xs text-slate-400 mt-2">
                        Are you sure you want to delete user <strong class="text-white" x-text="deleteUser.name"></strong>? This action is permanent and cannot be undone.
                    </p>
                </div>

                <form :action="'/admin/users/' + deleteUser.id" method="POST" class="pt-6 flex gap-2">
                    @csrf
                    @method('DELETE')
                    <button @click="deleteModal = false" type="button" class="flex-1 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-2 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-500 text-white transition-colors shadow-md shadow-rose-600/30">
                        Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
