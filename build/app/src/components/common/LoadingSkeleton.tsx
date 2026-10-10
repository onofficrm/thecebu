import React from 'react';

interface LoadingSkeletonProps {
  type?: 'card' | 'list' | 'grid' | 'banner';
  count?: number;
}

export const LoadingSkeleton: React.FC<LoadingSkeletonProps> = ({
  type = 'card',
  count = 3,
}) => {
  const items = Array.from({ length: count });

  if (type === 'banner') {
    return (
      <div className="w-full h-32 bg-[#E2EBF0] animate-pulse rounded-2xl mb-4" />
    );
  }

  if (type === 'grid') {
    return (
      <div className="grid grid-cols-3 gap-2.5 mb-4">
        {items.map((_, i) => (
          <div
            key={i}
            className="h-20 bg-white border border-[#E2EBF0] rounded-2xl p-3 flex flex-col items-center justify-center animate-pulse"
          >
            <div className="w-8 h-8 rounded-xl bg-[#E8EFF4] mb-2" />
            <div className="w-12 h-2.5 bg-[#E8EFF4] rounded" />
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className="space-y-3 mb-4">
      {items.map((_, i) => (
        <div
          key={i}
          className="bg-white border border-[#E2EBF0] rounded-2xl p-3.5 flex gap-3 animate-pulse"
        >
          <div className="w-20 h-20 bg-[#E8EFF4] rounded-xl shrink-0" />
          <div className="flex-1 space-y-2 py-1">
            <div className="w-16 h-3 bg-[#E8EFF4] rounded" />
            <div className="w-full h-4 bg-[#E8EFF4] rounded" />
            <div className="w-3/4 h-3 bg-[#E8EFF4] rounded" />
            <div className="w-1/2 h-3 bg-[#E8EFF4] rounded mt-2" />
          </div>
        </div>
      ))}
    </div>
  );
};
