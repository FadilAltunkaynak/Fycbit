import React, {
  createContext,
  useContext,
  useRef,
  useState,
  ReactNode,
  useEffect,
} from "react";
import { createPortal } from "react-dom";
import { cn } from "helpers/functions";

type DropdownContextType = {
  open: boolean;
  toggle: () => void;
  close: () => void;
  triggerRef: React.RefObject<HTMLButtonElement>;
};

const DropdownContext = createContext<DropdownContextType | null>(null);

export const useDropdown = () => {
  const ctx = useContext(DropdownContext);
  if (!ctx) throw new Error("Dropdown components must be inside <Dropdown>");
  return ctx;
};

type DropdownProps = {
  children: ReactNode;
};

export const Dropdown = ({ children }: DropdownProps) => {
  const [open, setOpen] = useState(false);
  const triggerRef = useRef<HTMLButtonElement>(null);

  const toggle = () => setOpen((p) => !p);
  const close = () => setOpen(false);

  return (
    <DropdownContext.Provider value={{ open, toggle, close, triggerRef }}>
      <div className="tradex-relative tradex-inline-block">{children}</div>
    </DropdownContext.Provider>
  );
};

type TriggerProps = {
  children: ReactNode;
  className?: string;
};

export const DropdownTrigger = ({ children, className }: TriggerProps) => {
  const { toggle, triggerRef } = useDropdown();

  return (
    <button
      ref={triggerRef}
      onClick={(e) => {
        e.stopPropagation();
        toggle();
      }}
      className={cn(
        "tradex-inline-flex tradex-items-center tradex-gap-1",
        className,
      )}
    >
      {children}
    </button>
  );
};

type ContentProps = {
  children: ReactNode;
  className?: string;
};

export const DropdownContent = ({ children, className }: ContentProps) => {
  const { open, close, triggerRef } = useDropdown();
  const contentRef = useRef<HTMLDivElement>(null);
  const [pos, setPos] = useState({ top: 0, left: 0 });

  useEffect(() => {
    if (!open || !triggerRef.current) return;

    const rect = triggerRef.current.getBoundingClientRect();
    setPos({
      top: rect.bottom + window.scrollY + 6,
      left: rect.left + window.scrollX,
    });
  }, [open]);

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      const target = e.target as Node;

      if (triggerRef.current?.contains(target)) return;
      if (contentRef.current?.contains(target)) return;

      close();
    };

    if (open) document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, [open]);

  if (!open) return null;

  return createPortal(
    <div
      ref={contentRef}
      style={{ top: pos.top, left: pos.left }}
      className={cn(
        "tradex-fixed tradex-z-50 tradex-bg-background-main tradex-border tradex-border-border tradex-shadow-lg tradex-rounded-md tradex-min-w-[180px] tradex-p-1",
        className,
      )}
    >
      {children}
    </div>,
    document.body,
  );
};

type ItemProps = {
  children: ReactNode;
  onClick?: () => void;
  className?: string;
};

export const DropdownItem = ({ children, onClick, className }: ItemProps) => {
  const { close } = useDropdown();

  return (
    <button
      onClick={() => {
        onClick?.();
        close();
      }}
      className={cn(
        "tradex-flex tradex-w-full tradex-text-left tradex-px-2 tradex-py-1.5 tradex-text-sm tradex-rounded-sm tradex-hover:bg-background-secondary tradex-transition",
        className,
      )}
    >
      {children}
    </button>
  );
};

export const DropdownSeparator = () => (
  <div className="tradex-my-1 tradex-h-px tradex-bg-border" />
);
