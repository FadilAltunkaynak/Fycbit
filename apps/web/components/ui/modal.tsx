import { cn } from "helpers/functions";
import React, { createContext, ReactNode, useContext } from "react";
import { createPortal } from "react-dom";

type ModalContextType = {
  close: () => void;
};

const ModalContext = createContext<ModalContextType | undefined>(undefined);

export const useModal = () => {
  const context = useContext(ModalContext);
  if (!context) throw new Error("useModal must be used inside Modal");
  return context;
};

type ModalProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  children: ReactNode;
  className?: string;
};

export const Modal = ({
  open,
  onOpenChange,
  className = "",
  children,
}: ModalProps) => {
  if (!open) return null;

  return createPortal(
    <ModalContext.Provider value={{ close: () => onOpenChange(false) }}>
      <div className="tradex-fixed tradex-inset-0 tradex-z-50 tradex-flex tradex-items-center tradex-justify-center">
        <div
          className="tradex-fixed tradex-inset-0 tradex-bg-slate-900/50 tradex-bg-opacity-50 tradex-backdrop-blur-sm"
          onClick={() => onOpenChange(false)}
        />

        <div
          className={cn(
            "tradex-relative tradex-bg-background-main tradex-rounded-lg tradex-shadow-lg  tradex-w-full tradex-mx-4 tradex-overflow-hidden tradex-max-w-3xl",
            className,
          )}
        >
          {children}
        </div>
      </div>
    </ModalContext.Provider>,
    document.body,
  );
};

type ModalCompoundProps = { children: ReactNode };

export const ModalHeader = ({ children }: ModalCompoundProps) => {
  const { close } = useModal();
  return (
    <div className="tradex-flex tradex-items-center tradex-justify-between tradex-p-4 tradex-border-b tradex-border-border">
      <h5 className="tradex-text-lg tradex-font-semibold">{children}</h5>
      <button
        onClick={close}
        className="tradex-text-gray-500 tradex-hover:text-primary"
      >
        &times;
      </button>
    </div>
  );
};

export const ModalBody = ({ children }: ModalCompoundProps) => (
  <div className="tradex-p-4">{children}</div>
);

export const ModalFooter = ({ children }: ModalCompoundProps) => (
  <div className="tradex-p-4 tradex-border-t tradex-border-border tradex-flex tradex-justify-center">
    {children}
  </div>
);
