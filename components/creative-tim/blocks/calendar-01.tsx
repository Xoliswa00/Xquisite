"use client"

import * as React from "react"
import {
  addDays,
  addWeeks,
  format,
  isSameDay,
  startOfWeek,
  subWeeks,
} from "date-fns"
import {
  ChevronLeft,
  ChevronRight,
  MoreVertical,
  Pencil,
  Plus,
} from "lucide-react"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

interface MeetingCardProps {
  title: string
  time: string
  link: string
}

function MeetingCard({ title, time, link }: MeetingCardProps) {
  return (
    <Card className="border">
      <CardHeader className="m-0 w-full flex-row items-start justify-between p-4">
        <div>
          <CardTitle className="mb-1 text-lg font-semibold">{title}</CardTitle>
          <p className="text-muted-foreground text-sm">{time}</p>
        </div>
        <div className="flex items-center">
          <Button size="icon" variant="ghost" className="h-8 w-8">
            <Pencil className="h-4 w-4" />
          </Button>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button size="icon" variant="ghost" className="h-8 w-8">
                <MoreVertical className="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem>Edit</DropdownMenuItem>
              <DropdownMenuItem>Delete</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </CardHeader>
      <CardContent className="px-4 pt-2 pb-4">
        <div className="flex items-center gap-1">
          <div className="flex items-center -space-x-3">
            <Avatar className="border-background h-8 w-8 border-2 hover:z-10 focus:z-10">
              <AvatarImage
                src="https://v3.material-tailwind.com/man-profile-1.jpg"
                alt="user 1"
              />
              <AvatarFallback>U1</AvatarFallback>
            </Avatar>
            <Avatar className="border-background h-8 w-8 border-2 hover:z-10 focus:z-10">
              <AvatarImage
                src="https://v3.material-tailwind.com/man-profile-2.jpg"
                alt="user 2"
              />
              <AvatarFallback>U2</AvatarFallback>
            </Avatar>
            <Avatar className="border-background h-8 w-8 border-2 hover:z-10 focus:z-10">
              <AvatarImage
                src="https://v3.material-tailwind.com/man-profile-3.jpg"
                alt="user 3"
              />
              <AvatarFallback>U3</AvatarFallback>
            </Avatar>
          </div>
          <Button
            size="icon"
            variant="outline"
            className="h-8 w-8 rounded-full"
          >
            <Plus className="h-4 w-4" />
          </Button>
        </div>
        <div className="mt-4">
          <Label
            htmlFor={`meeting-link-${title}`}
            className="mb-2 block text-left text-sm font-semibold"
          >
            Meeting Link
          </Label>
          <Input
            id={`meeting-link-${title}`}
            name="meeting-link"
            defaultValue={link}
            className="rounded"
          />
        </div>
      </CardContent>
    </Card>
  )
}

const EVENTS = [
  {
    title: "Finalize Report",
    time: "09:00 AM - 11:00 AM",
    link: "https://meet/abcns-nckwn-ismvl",
  },
  {
    title: "Design Review",
    time: "12:00 PM - 1:00 PM",
    link: "https://meet/abcns-nckwn-ncnm",
  },
]

export default function Calendar01() {
  const [selectedDate, setSelectedDate] = React.useState<Date>(new Date())
  const [weekStart, setWeekStart] = React.useState<Date>(
    startOfWeek(new Date(), { weekStartsOn: 1 })
  )

  const weekDays = React.useMemo(() => {
    return Array.from({ length: 7 }, (_, i) => addDays(weekStart, i))
  }, [weekStart])

  const handlePreviousWeek = () => {
    setWeekStart(subWeeks(weekStart, 1))
  }

  const handleNextWeek = () => {
    setWeekStart(addWeeks(weekStart, 1))
  }

  const handleToday = () => {
    const today = new Date()
    setWeekStart(startOfWeek(today, { weekStartsOn: 1 }))
    setSelectedDate(today)
  }

  return (
    <Card className="mx-auto max-w-xl border">
      <CardHeader className="m-0 w-full flex-wrap items-start justify-between gap-6 p-6">
        <CardTitle className="text-xl font-semibold">Weekly Schedule</CardTitle>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="icon" onClick={handlePreviousWeek}>
            <ChevronLeft className="h-4 w-4" />
          </Button>
          <Button variant="outline" size="icon" onClick={handleNextWeek}>
            <ChevronRight className="h-4 w-4" />
          </Button>
          <Button variant="outline" onClick={handleToday}>
            Today
          </Button>
          <Button variant="outline" size="icon">
            <Plus className="h-4 w-4" />
          </Button>
        </div>
      </CardHeader>
      <CardContent className="px-6 pt-2 pb-6">
        <div className="mb-6 flex flex-wrap justify-between gap-1">
          {weekDays.map((date, index) => {
            const isSelected = isSameDay(date, selectedDate)
            const isToday = isSameDay(date, new Date())

            return (
              <Button
                key={index}
                className="flex-col gap-y-1"
                variant={isSelected ? "default" : "ghost"}
                onClick={() => setSelectedDate(date)}
              >
                <span className="opacity-70">{format(date, "EEE")}</span>
                <span className={isToday && !isSelected ? "font-bold" : ""}>
                  {format(date, "d")}
                </span>
              </Button>
            )
          })}
        </div>
        <div className="space-y-4">
          {EVENTS.map((props, key) => (
            <MeetingCard key={key} {...props} />
          ))}
        </div>
      </CardContent>
    </Card>
  )
}
