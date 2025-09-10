import React from "react";
import { cn } from "@/lib/utils";

const Legend = React.forwardRef(({ className, ...props }, ref) => (
  <legend
    ref={ref}
    className={cn(
      "text-sm font-medium leading-none text-gray-700",
      className
    )}
    style={{ color: 'white' }}
    {...props}
  />
));

Legend.displayName = "Legend";

export { Legend };
