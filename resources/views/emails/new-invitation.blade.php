<!DOCTYPE html>
<html>
<head>
    <title>New Research Proposal Invitation</title>
</head>
<body>
    <h2>New Research Proposal Invitation</h2>
    
    <p>Dear {{ $invitation->lecturer->user->name }},</p>
    
    <p>You have received a new research proposal invitation from {{ $invitation->student->user->name }}.</p>
    
    @if($invitation->proposal)
    <h3>Proposal Details:</h3>
    <p><strong>Title:</strong> {{ $invitation->proposal->title }}</p>
    <p><strong>Field:</strong> {{ $invitation->proposal->field }}</p>
    @if($invitation->proposal->description)
    <p><strong>Description:</strong> {{ $invitation->proposal->description }}</p>
    @endif
    @endif
    
    <p>Please review and respond to this invitation within 72 hours. After this period, the invitation will be automatically processed based on your current capacity.</p>
    
    <p>You can manage this invitation by logging into the system.</p>
    
    <p>Best regards,<br>Research Management System</p>
</body>
</html> 