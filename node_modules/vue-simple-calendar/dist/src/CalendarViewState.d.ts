import { INormalizedCalendarItem } from "./ICalendarItem";
declare class CalendarViewState {
    currentDragItem?: INormalizedCalendarItem;
    dateSelectionOrigin?: Date;
    currentHoveredItemId: string;
    CalendarMath: object;
}
export default CalendarViewState;
