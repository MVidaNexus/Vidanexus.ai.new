@extends('admin.horizon.layout')

@section('title', 'Phase: v2.0 To do')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    <div style="background: linear-gradient(135deg, rgba(0, 168, 230, 0.1), rgba(191, 0, 255, 0.1)); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 2.5rem; margin-bottom: 3rem; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: var(--primary-admin); filter: blur(100px); opacity: 0.1;"></div>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem;">
            <div>
                <h2 style="font-family: 'Poppins', sans-serif; font-size: 2rem; margin: 0 0 1rem; color: var(--text-main);">
                    <i class="fas fa-rocket" style="color: var(--primary-admin); margin-right: 0.75rem;"></i> Evolution Phase: v2.0
                </h2>
                <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; max-width: 700px; margin: 0;">
                    This roadmap tracks the next generation of AI tools and automation features. 
                    Manage milestones and system "notes" directly from this command center.
                </p>
            </div>
            <button onclick="openModal('addFeatureModal')" style="background: var(--primary-admin); color: #000; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s ease; flex-shrink: 0;">
                <i class="fas fa-plus"></i> Add Milestone
            </button>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        @forelse($features as $feature)
            <div class="card-admin" style="display: flex; align-items: flex-start; gap: 1.5rem; padding: 1.75rem; transition: transform 0.2s ease; position: relative; group;">
                <div style="width: 54px; height: 54px; border-radius: 16px; background: {{ $feature->color }}20; border: 1px solid {{ $feature->color }}40; display: flex; align-items: center; justify-content: center; color: {{ $feature->color }}; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="{{ $feature->icon }}"></i>
                </div>
                
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h3 style="font-size: 1.2rem; margin: 0; color: var(--text-main);">{{ $feature->title }}</h3>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 32px; background: {{ $feature->status == 'Implementing' ? '#f59e0b20' : ($feature->status == 'Completed' ? 'var(--horizon-success-bg)' : 'rgba(255,255,255,0.05)') }}; color: {{ $feature->status == 'Implementing' ? '#f59e0b' : ($feature->status == 'Completed' ? 'var(--horizon-success)' : 'var(--text-muted)') }}; border: 1px solid {{ $feature->status == 'Implementing' ? '#f59e0b40' : ($feature->status == 'Completed' ? 'var(--horizon-success)' : 'var(--horizon-border)') }};">
                                {{ $feature->status }}
                            </span>
                            <div class="feature-actions" style="display: flex; gap: 0.5rem;">
                                <button onclick="editFeature({{ $feature }})" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem;"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.horizon.roadmap.destroy', $feature->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this feature?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0.25rem;"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <p style="color: var(--text-muted); line-height: 1.5; margin: 0; font-size: 0.95rem;">
                        {{ $feature->description }}
                    </p>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 4rem; color: var(--text-muted); background: var(--horizon-nav-hover); border-radius: 24px; border: 1px dashed var(--horizon-border);">
                <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.2;"></i>
                <p>No roadmap items recorded yet. Start by adding your first milestone!</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Modal -->
<div id="addFeatureModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card-admin" style="width: 100%; max-width: 600px; margin: 2rem;">
        <h3 style="margin: 0 0 2rem; color: var(--primary-admin);">Add Roadmap Milestone</h3>
        <form action="{{ route('admin.horizon.roadmap.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Title</label>
                <input type="text" name="title" required class="modal-input" placeholder="e.g. Mobile Optimization">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Description</label>
                <textarea name="description" required class="modal-input" style="height: 100px;" placeholder="What are we building?"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Status</label>
                    <select name="status" class="modal-input">
                        <option value="Planned">Planned</option>
                        <option value="Implementing">Implementing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Color</label>
                    <input type="color" name="color" value="#00A8E6" class="modal-input" style="height: 42px; padding: 4px;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">FA Icon Class</label>
                    <input type="text" name="icon" value="fas fa-rocket" class="modal-input" placeholder="fas fa-star">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Order</label>
                    <input type="number" name="order" value="0" class="modal-input">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="button" onclick="closeModal('addFeatureModal')" style="background: none; border: 1px solid var(--horizon-border); color: var(--text-muted); padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-save">Create Milestone</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editFeatureModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card-admin" style="width: 100%; max-width: 600px; margin: 2rem;">
        <h3 style="margin: 0 0 2rem; color: var(--primary-admin);">Edit Milestone</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Title</label>
                <input type="text" name="title" id="edit_title" required class="modal-input">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Description</label>
                <textarea name="description" id="edit_description" required class="modal-input" style="height: 100px;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Status</label>
                    <select name="status" id="edit_status" class="modal-input">
                        <option value="Planned">Planned</option>
                        <option value="Implementing">Implementing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Color</label>
                    <input type="color" name="color" id="edit_color" class="modal-input" style="height: 42px; padding: 4px;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">FA Icon Class</label>
                    <input type="text" name="icon" id="edit_icon" class="modal-input">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Order</label>
                    <input type="number" name="order" id="edit_order" class="modal-input">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="button" onclick="closeModal('editFeatureModal')" style="background: none; border: 1px solid var(--horizon-border); color: var(--text-muted); padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-save">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
    .card-admin:hover {
        transform: translateX(8px);
        border-color: var(--primary-admin);
    }
    .modal-input {
        width: 100%;
        background: #0a0f19;
        border: 1px solid var(--horizon-border);
        border-radius: 10px;
        color: #fff;
        padding: 0.75rem;
        outline: none;
        box-sizing: border-box;
    }
    .modal-input:focus {
        border-color: var(--primary-admin);
    }
    .modal-backdrop {
        display: none;
    }
</style>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    function editFeature(feature) {
        document.getElementById('editForm').action = '/horizon-admin/roadmap/' + feature.id;
        document.getElementById('edit_title').value = feature.title;
        document.getElementById('edit_description').value = feature.description;
        document.getElementById('edit_status').value = feature.status;
        document.getElementById('edit_color').value = feature.color;
        document.getElementById('edit_icon').value = feature.icon;
        document.getElementById('edit_order').value = feature.order;
        openModal('editFeatureModal');
    }
    
    // Auto-close on backdrop click
    window.onclick = function(event) {
        if (event.target.className === 'modal-backdrop') {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection

