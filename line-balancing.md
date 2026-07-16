# Sewing Department Line Balancing Module Plan

This module enables industrial engineers to manage machine allocation, operator attendance, and line balancing within the sewing department.

## 1. Database & Model Structure
Leverage the existing schema for Zones, Parts, Assembly Lines, and Machines.

*   **Relationships**: Ensure correct associations between `Department`, `Zone`, `Part`, `AssemblyLine`, `Machine`, and `Operator`.
*   **Tracking**: Utilize or create a `MachineAssignment` model to track `machine_id`, `operator_id`, and `date`.
*   **Operator Data**: Enhance `Operator` model to include performance metrics.

## 2. Workflow Implementation

### Phase 1: Setup & Access Control
*   Define `industrial_engineer` role/permission.
*   Create a route group for access to the Sewing Department dashboard.

### Phase 2: User Interface
*   **Dashboard**: Display 5 zones with drill-down to `Parts` and `Assembly Lines`.
*   **Machine Grid**: Visualize 15 machines per Part.
    *   **Green**: Machine occupied.
    *   **Red**: Machine vacant.

### Phase 3: Data Ingestion & Logic
*   **Excel Upload**: Use `maatwebsite/excel` for:
    *   Attendance Data (`operator_id`, status).
    *   Performance Data (`operator_id`, metrics).
*   **Balancing Logic**:
    1.  Parse attendance to identify vacancies.
    2.  Filter present, unassigned operators.
    3.  Rank operators by performance.
    4.  Assign top performers to vacant machines.

## 3. Recommended Implementation Steps
1.  **Authorization**: Implement middleware/policy for `industrial_engineer`.
2.  **Controller**: Create `SewingDepartmentController` for hierarchy and dashboard display.
3.  **Service**: Create `LineBalancingService` for Excel parsing and assignment algorithm.
4.  **UI**: Build the visual grid using Bootstrap's grid system and component classes.
5.  **Validation**: Add file validation for Excel imports.
