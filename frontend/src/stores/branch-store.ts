import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface BranchState {
  selectedBranchId: number | null;
  setBranch: (id: number) => void;
}

export const useBranchStore = create<BranchState>()(
  persist(
    (set) => ({
      selectedBranchId: null,
      setBranch: (id) => set({ selectedBranchId: id }),
    }),
    { name: 'scrm.branch' },
  ),
);
