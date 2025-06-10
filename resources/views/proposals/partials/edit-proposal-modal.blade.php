<!-- Edit Proposal Modal -->
<div class="modal fade" id="editProposalModal" tabindex="-1" aria-labelledby="editProposalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProposalModalLabel">Sửa đề tài nghiên cứu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProposalForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="editProposalId" name="proposal_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editField" class="form-label">Lĩnh vực</label>
                        <input type="text" class="form-control" id="editField" name="field" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('editProposalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const proposalId = document.getElementById('editProposalId').value;
    const form = this;
    form.action = `/proposals/${proposalId}`;
    form.submit();
});
</script>
@endpush 