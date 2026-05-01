# UML Diagram Guide

This folder contains the report-ready UML class diagrams derived from the Laravel models, migrations, routes, and the main business-flow controllers/services.

## Files

- `class-diagram-overview.puml`
  - Balanced high-level view of the whole school management system.
  - Combines the main business entities across accounts, academics, timetable, finance, and communication.

- `class-diagram-academic.puml`
  - Focuses on language programs, courses, classes, scheduling, attendance, and teacher resources.
  - Best for explaining how academic delivery is structured.

- `class-diagram-finance.puml`
  - Focuses on tuition setup, student payments, employee payments, and scholarship activations.
  - The dotted link between `StudentTuition` and `TuitionPayment` represents the business flow used by the financial services, even though payments are stored through user-linked records.

- `class-diagram-communication.puml`
  - Focuses on user-to-user messaging, notifications, reviews, and teacher resources shared with classes.
  - Best for explaining user interaction and information sharing.

- `class-diagram-approval-notifications.puml`
  - Focuses on account approval, role assignment, requested courses, student tuition creation after approval, and approval notifications.
  - Best for explaining onboarding and administrative account control.

- `class-diagram.puml`
  - Legacy entry file that mirrors the overview diagram.

## Scope Choices

- Internal Laravel tables such as cache, sessions, password resets, jobs, and personal access tokens are intentionally excluded.
- Vendor internals are omitted except for the `Role` concept because role-based access is central to the system.
- Notifications are shown as a business entity because the application uses database notifications heavily in user-facing flows.
- Pivot storage such as `class_student` is represented as a direct enrollment relationship to keep the diagrams readable.

## Suggested Report Usage

1. Use `class-diagram-overview.puml` in the main system design chapter.
2. Use the focused diagrams in separate subsections for academic flow, finance, communication, and approval/account management.
3. Export each `.puml` file to PNG or SVG for insertion into the final Word report.
