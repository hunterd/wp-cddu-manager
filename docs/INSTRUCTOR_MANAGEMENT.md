# Instructor Management System

This document describes the instructor assignment functionality for the CDDU Manager WordPress plugin.

## Overview

The instructor management system allows administrators to assign and manage instructors within organizations. This is a prerequisite for creating contracts, as instructors must be assigned to organizations before contracts can be established.

## Features

### 1. Admin Interface for Instructor Management

#### Access
- **Menu Location**: WordPress Admin → Organizations → Manage Instructors
- **Required Capability**: `cddu_manage_instructors` or `manage_options`

#### Functionality
- **Organization Selection**: Choose from available organizations
- **Instructor Search**: Real-time search for available instructors
- **Assignment Management**: Assign/unassign instructors to/from organizations
- **Status Tracking**: View assignment dates and active contract counts
- **Validation**: Prevents unassignment when instructors have active contracts

### 2. REST API Endpoints

Base URL: `/wp-json/cddu-manager/v1/instructor-organizations/`

#### Assign Instructor
```http
POST /assign
Content-Type: application/json

{
    "organization_id": 123,
    "instructor_id": 456
}
```

#### Unassign Instructor
```http
DELETE /unassign
Content-Type: application/json

{
    "organization_id": 123,
    "instructor_id": 456
}
```

#### Get Organization Instructors
```http
GET /{organization_id}/instructors
```

#### Get Instructor Organizations
```http
GET /{instructor_id}/organizations
```

#### Search Instructors
```http
GET /search?search=term&organization_id=123&assigned=true
```

### 3. Role-Based Access Control

#### Custom Capabilities
- `cddu_manage_instructors`: Manage instructor assignments
- `cddu_manage`: General CDDU system management
- `cddu_view_contracts`: View contracts
- `cddu_create_contracts`: Create contracts
- `cddu_edit_contracts`: Edit contracts
- `cddu_delete_contracts`: Delete contracts
- `cddu_manage_organizations`: Manage organizations
- `cddu_manage_signatures`: Manage electronic signatures

#### Custom Roles
- **CDDU Organization Manager**: Can manage instructors and contracts for assigned organizations
- **CDDU Instructor**: Can view their own contracts and submit timesheets
- **CDDU Administrator**: Full system access

### 4. Security Features

#### Input Validation
- Nonce verification for all AJAX requests
- Capability checks on all operations
- Input sanitization and validation
- Post type verification

#### Permission Checks
- Organization-specific permissions for managers
- Global permissions for administrators
- Instructor-specific access controls

#### Data Integrity
- Prevents duplicate assignments
- Validates organization and instructor existence
- Checks for active contracts before unassignment

## Usage Instructions

### For Organization Managers

1. **Access the Interface**
   - Navigate to Organizations → Manage Instructors in WordPress admin
   - Select your organization from the dropdown

2. **View Assigned Instructors**
   - See all currently assigned instructors
   - View assignment dates and active contract counts
   - Identify instructors with active contracts (cannot be unassigned)

3. **Search and Assign New Instructors**
   - Use the search box to find available instructors
   - Click "Assign" next to any instructor to add them to your organization
   - Real-time search filters results as you type

4. **Unassign Instructors**
   - Click "Unassign" next to any instructor without active contracts
   - Confirm the action in the dialog
   - Instructors with active contracts cannot be unassigned

### For Developers

#### Adding Custom Validation
```php
add_filter('cddu_validate_instructor_assignment', function($errors, $org_id, $instructor_id) {
    // Custom validation logic
    if (/* your condition */) {
        $errors[] = __('Custom validation error', 'your-domain');
    }
    return $errors;
}, 10, 3);
```

#### Hooking into Assignment Events
```php
// After instructor assignment
add_action('cddu_instructor_assigned', function($org_id, $instructor_id) {
    // Custom logic after assignment
}, 10, 2);

// After instructor unassignment
add_action('cddu_instructor_unassigned', function($org_id, $instructor_id) {
    // Custom logic after unassignment
}, 10, 2);
```

## Database Schema

### Post Meta Fields

#### Organizations (`cddu_organization`)
- `assigned_instructors`: Serialized array of instructor IDs
- `instructor_assignment_history`: Assignment/unassignment history

#### Instructors (`cddu_instructor`)
- `assigned_organizations`: Serialized array of organization IDs
- `instructor_user_id`: Linked WordPress user ID

## Testing

### Unit Tests
Run the test suite to verify functionality:

```bash
cd /path/to/plugin
./vendor/bin/phpunit tests/InstructorAssignmentTest.php
./vendor/bin/phpunit tests/RoleManagerTest.php
./vendor/bin/phpunit tests/InstructorManagerTest.php
```

### Manual Testing Checklist

#### Basic Functionality
- [ ] Can access instructor management page
- [ ] Can select an organization
- [ ] Can view assigned instructors
- [ ] Can search for instructors
- [ ] Can assign instructor to organization
- [ ] Can unassign instructor from organization
- [ ] Cannot unassign instructor with active contracts

#### Security Testing
- [ ] Unauthorized users cannot access interface
- [ ] AJAX requests require valid nonces
- [ ] Users can only manage their assigned organizations
- [ ] Input validation prevents XSS/injection

#### Error Handling
- [ ] Proper error messages for invalid operations
- [ ] Graceful handling of non-existent organizations/instructors
- [ ] Network error handling in JavaScript

## Troubleshooting

### Common Issues

#### "Insufficient permissions" Error
- Verify user has `cddu_manage_instructors` capability
- Check if user is assigned as manager to the organization
- Ensure user role has proper capabilities

#### Instructor Not Appearing in Search
- Verify instructor post is published
- Check instructor has required metadata
- Ensure instructor is not already assigned (if filtering)

#### Cannot Unassign Instructor
- Check if instructor has active contracts
- Verify contracts are properly marked as completed/cancelled
- Review contract status in database

#### AJAX Requests Failing
- Check nonce values in browser developer tools
- Verify REST API is accessible
- Check WordPress error logs for PHP errors

### Debug Mode

Enable debug logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('CDDU_DEBUG', true);
```

### Support

For technical support or bug reports:
1. Check the WordPress error logs
2. Enable debug mode and reproduce the issue
3. Provide detailed steps to reproduce
4. Include relevant error messages and browser console logs

## Changelog

### Version 1.0.0
- Initial implementation of instructor assignment system
- REST API endpoints for instructor management
- Role-based access control
- Admin interface with search and assignment functionality
- Comprehensive test suite
- Security and validation features
