<!DOCTYPE html>
<html>
<head>
    <title>Research Proposal Invitation Update</title>
</head>
<body>
    <h2>Research Proposal Invitation Update</h2>
    
    <p>Dear {{ $invitation->student->user->name }},</p>
    
    <p>Your research proposal invitation to {{ $invitation->lecturer->user->name }} has been {{ $invitation->status }}.</p>
    
    @if($invitation->proposal)
    <h3>Proposal Details:</h3>
    <p><strong>Title:</strong> {{ $invitation->proposal->title }}</p>
    <p><strong>Field:</strong> {{ $invitation->proposal->field }}</p>
    @endif
    
    @if($invitation->status === 'accepted')
    <p>Congratulations! You can now proceed with your research under the guidance of {{ $invitation->lecturer->user->name }}.</p>
    @else
    <p>You may send a new invitation to another lecturer or try again later.</p>
    @endif
    
    <p>Best regards,<br>Research Management System</p>
</body>
</html> 