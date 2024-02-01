import { FC } from "react";
import { styles } from "../styles";
import { IContainer } from "../interface/IContainer";

const ContainerHome: FC<IContainer> = ({ children }) => {
  console.log("children", children);
  return (
    <div className={`bg-primary ${styles.flexStart}`}>
      <div className={`${styles.boxWidth}`}>{children}</div>
    </div>
  );
};

export default ContainerHome;
