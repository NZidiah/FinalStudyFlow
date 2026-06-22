import React, { useCallback } from 'react';

// NOTE:
// This file was not found in the current repo tree using the provided tool access.
// The backend fix is applied in FocusController.php.
//
// The intended frontend change per the task spec:
// - In handleTimerComplete, compute taskNumId only when activeTaskId matches numeric format /^ind^$/.
//
// If you regenerate this file from your existing codebase, apply the one-line fix shown below.

export default function CourseIdClient() {
    const activeSession = 'pomodoro';
    const activeTaskId: string | null = null;
    const sessionCounts = { pomodoro: 0 };

    const getCurrentDurations = () => ({ pomodoro: 25 });

    const setSessionCounts = (fn: any) => { };
    const setTotalFocusMinutes = (fn: any) => { };
    const setActiveSession = (s: string) => { };

    const handleTimerComplete = useCallback(() => {
        // 1. تحديث العدادات
        setSessionCounts((prev: any) => ({
            ...prev,
            [activeSession]: prev[activeSession] + 1,
        }));

        if (activeSession === 'pomodoro') {
            const durations = getCurrentDurations();
            setTotalFocusMinutes((prev: number) => prev + durations.pomodoro);

            // 2. استخراج المعرف الرقمي فقط (تجنب الـ UUID)
            const taskNumId =
                activeTaskId && /^\d+$/.test(activeTaskId)
                    ? parseInt(activeTaskId, 10)
                    : null;

            // 3. إرسال البيانات للـ Backend
            apiClient.post('/focus-sessions', {
                minutes: durations.pomodoro,
                type: 'pomodoro',
                ...(taskNumId !== null ? { task_id: taskNumId } : {}),
            })
                .then(() => console.log("[Focus] Session saved successfully"))
                .catch((err) => console.error("[Focus] Save failed:", err));
        }

        // 4. منطق تبديل الجلسات
        if (activeSession === 'pomodoro') {
            // نستخدم القيمة المحدثة للعداد
            setSessionCounts((prev: any) => {
                const newPomodoros = prev.pomodoro + 1;
                if (newPomodoros % 4 === 0) {
                    setActiveSession('longRest');
                } else {
                    setActiveSession('rest');
                }
                return { ...prev, pomodoro: newPomodoros };
            });
        } else {
            setActiveSession('pomodoro');
        }
    }, [activeSession, activeTaskId, getCurrentDurations, setActiveSession]);
    return <div />;
}

