import { useEffect } from 'react';
import { useBranches } from '@/features/branches/api';
import { useBranchStore } from '@/stores/branch-store';
import { Select } from '@/components/ui/Select';

export function BranchSwitcher() {
  const { data: branches } = useBranches();
  const selected = useBranchStore((s) => s.selectedBranchId);
  const setBranch = useBranchStore((s) => s.setBranch);

  // Keep the selection valid: if nothing is chosen, or a stale/invalid id is
  // persisted (e.g. from a previous dataset), fall back to the first branch.
  useEffect(() => {
    if (!branches || branches.length === 0) return;
    if (!branches.some((b) => b.id === selected)) {
      setBranch(branches[0].id);
    }
  }, [branches, selected, setBranch]);

  if (!branches || branches.length === 0) return null;

  return (
    <label className="flex items-center gap-2 text-xs text-slate-500">
      Branch
      <Select
        value={selected ?? ''}
        onChange={(e) => setBranch(Number(e.target.value))}
        className="w-40"
      >
        {branches.map((b) => (
          <option key={b.id} value={b.id}>
            {b.name}
          </option>
        ))}
      </Select>
    </label>
  );
}
