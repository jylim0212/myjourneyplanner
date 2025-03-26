<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Journey;
use App\Models\JourneyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        $users = User::where('is_admin', false)->get();
        return view('admin.users.index', compact('users'));
    }

    public function deleteUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot delete admin user');
        }

        try {
            DB::beginTransaction();

            // Delete the user (this will trigger the booted method in User model)
            $user->delete();

            DB::commit();
            return back()->with('success', 'User and all associated data deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete user and associated data: ' . $e->getMessage());
        }
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot modify admin user');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            DB::commit();
            return back()->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function weatherApi()
    {
        return view('admin.weather.index');
    }

    public function gptApi()
    {
        return view('admin.gpt.index');
    }
} 