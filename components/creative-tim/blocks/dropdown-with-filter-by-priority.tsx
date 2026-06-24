"use client"

import { Archive } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

export default function DropdownWithFilterByPriority() {
  return (
    <div className="text-center">
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button>By Priority</Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent className="w-auto p-4">
          <DropdownMenuItem className="flex cursor-pointer items-center gap-2 p-2">
            <Archive className="h-4 w-4 text-slate-800" />
            All Priorities
          </DropdownMenuItem>
          <DropdownMenuItem className="flex cursor-pointer items-center gap-2 p-2">
            <Archive className="h-4 w-4 text-red-500" />
            Urgent
          </DropdownMenuItem>
          <DropdownMenuItem className="flex cursor-pointer items-center gap-2 p-2">
            <Archive className="h-4 w-4 text-amber-500" />
            High
          </DropdownMenuItem>
          <DropdownMenuItem className="flex cursor-pointer items-center gap-2 p-2">
            <Archive className="h-4 w-4 text-green-500" />
            Normal
          </DropdownMenuItem>
          <DropdownMenuItem className="flex cursor-pointer items-center gap-2 p-2">
            <Archive className="h-4 w-4 text-blue-500" />
            Low
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}
