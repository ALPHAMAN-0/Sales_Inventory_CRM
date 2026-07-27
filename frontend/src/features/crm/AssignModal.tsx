import { useState } from 'react';
import { Button } from '@/components/ui/Button';
import { FormField } from '@/components/ui/FormField';
import { Modal } from '@/components/ui/Modal';
import { Select } from '@/components/ui/Select';
import { ApiError } from '@/lib/ApiError';
import { useKpiDashboard } from '@/features/kpi/api';
import { toast } from '@/stores/toast-store';
import type { LostCustomer } from '@/types/api';
import { useAssignCustomer } from './api';

interface Props {
  open: boolean;
  onClose: () => void;
  customer: LostCustomer | null;
}

export function AssignModal({ open, onClose, customer }: Props) {
  const employees = useKpiDashboard({});
  const assign = useAssignCustomer();
  const [employeeId, setEmployeeId] = useState<number | ''>('');

  const submit = () => {
    if (!customer || employeeId === '') return;
    assign.mutate(
      { customerId: customer.id, employeeId: Number(employeeId) },
      {
        onSuccess: () => {
          toast.success(`${customer.name} assigned for recovery.`);
          setEmployeeId('');
          onClose();
        },
        onError: (error) => {
          toast.error(
            error instanceof ApiError ? error.message : 'Assignment failed.',
          );
        },
      },
    );
  };

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={customer ? `Assign ${customer.name}` : 'Assign customer'}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button
            onClick={submit}
            loading={assign.isPending}
            disabled={employeeId === ''}
          >
            Assign for recovery
          </Button>
        </>
      }
    >
      <FormField
        label="Assign to employee"
        htmlFor="assignee"
        hint="The employee earns KPI points when this customer buys again."
      >
        <Select
          id="assignee"
          value={employeeId}
          onChange={(e) =>
            setEmployeeId(e.target.value ? Number(e.target.value) : '')
          }
        >
          <option value="">Select an employee…</option>
          {employees.data?.map((emp) => (
            <option key={emp.employee_id} value={emp.employee_id}>
              {emp.employee_name}
              {emp.branch_name ? ` · ${emp.branch_name}` : ''}
            </option>
          ))}
        </Select>
      </FormField>
    </Modal>
  );
}
