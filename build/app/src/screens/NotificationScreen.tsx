import React, { useState } from 'react';
import { MOCK_NOTIFICATIONS_LIST } from '../data/mockData';
import { NotificationItem } from '../types';
import { Bell, CheckCheck, Trash2, Megaphone, Ticket, FileText, Info } from 'lucide-react';
import { EmptyState } from '../components/common/EmptyState';

interface NotificationScreenProps {
  onSelectNotification?: (item: NotificationItem) => void;
}

export const NotificationScreen: React.FC<NotificationScreenProps> = () => {
  const [notifications, setNotifications] = useState<NotificationItem[]>(
    MOCK_NOTIFICATIONS_LIST
  );
  const [filter, setFilter] = useState<string>('all');

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((n) => ({ ...n, isRead: true })));
  };

  const removeNotification = (id: string) => {
    setNotifications((prev) => prev.filter((n) => n.id !== id));
  };

  const filtered = notifications.filter((n) => {
    if (filter === 'all') return true;
    if (filter === 'notice') return n.category === '공지';
    if (filter === 'coupon') return n.category === '쿠폰';
    return true;
  });

  const getIcon = (cat: string) => {
    switch (cat) {
      case '공지':
        return <Megaphone size={16} className="text-[#075A9D]" />;
      case '쿠폰':
        return <Ticket size={16} className="text-[#079BE8]" />;
      case '새글':
        return <FileText size={16} className="text-[#0879E7]" />;
      default:
        return <Info size={16} className="text-[#617789]" />;
    }
  };

  return (
    <div className="space-y-4 pb-24">
      {/* Top Controls */}
      <div className="flex items-center justify-between px-1">
        <div className="flex items-center gap-1.5">
          <button
            type="button"
            onClick={() => setFilter('all')}
            className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
              filter === 'all'
                ? 'bg-[#079BE8] text-white shadow-xs'
                : 'bg-white text-[#617789] border border-[#E1ECF3]'
            }`}
          >
            전체
          </button>
          <button
            type="button"
            onClick={() => setFilter('notice')}
            className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
              filter === 'notice'
                ? 'bg-[#079BE8] text-white shadow-xs'
                : 'bg-white text-[#617789] border border-[#E1ECF3]'
            }`}
          >
            공지
          </button>
          <button
            type="button"
            onClick={() => setFilter('coupon')}
            className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
              filter === 'coupon'
                ? 'bg-[#079BE8] text-white shadow-xs'
                : 'bg-white text-[#617789] border border-[#E1ECF3]'
            }`}
          >
            쿠폰/혜택
          </button>
        </div>

        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={markAllAsRead}
            title="모두 읽음"
            className="text-[11px] font-semibold text-[#079BE8] hover:underline flex items-center gap-1"
          >
            <CheckCheck size={14} />
            <span>모두 읽음</span>
          </button>
        </div>
      </div>

      {/* Notifications List */}
      {filtered.length === 0 ? (
        <EmptyState
          title="새로운 알림이 없습니다"
          description="주요 필리핀 소식, 쿠폰 마감 정보, 긴급 공지가 여기에 표시됩니다."
          icon={<Bell size={24} />}
        />
      ) : (
        <div className="space-y-2.5">
          {filtered.map((item) => (
            <div
              key={item.id}
              className={`p-3.5 rounded-2xl border transition-colors relative ${
                item.isRead
                  ? 'bg-white border-[#E1ECF3]'
                  : 'bg-[#F7FBFE] border-[#B9E5FC]'
              }`}
            >
              <div className="flex items-start gap-3">
                <div className="w-8 h-8 rounded-xl bg-white border border-[#E1ECF3] flex items-center justify-center shrink-0">
                  {getIcon(item.category)}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between gap-1 mb-0.5">
                    <span className="text-[11px] font-bold text-[#079BE8]">
                      {item.category}
                    </span>
                    <span className="text-[10px] text-[#8799A8]">
                      {item.timeAgo}
                    </span>
                  </div>
                  <h3 className="text-xs font-bold text-[#183247] leading-snug">
                    {item.title}
                  </h3>
                  <p className="text-xs text-[#617789] mt-1 leading-normal">
                    {item.content}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={() => removeNotification(item.id)}
                  aria-label="알림 삭제"
                  className="text-[#8799A8] hover:text-[#0879E7] p-1 -mr-1"
                >
                  <Trash2 size={14} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};
