import { FC, useState } from "react";
import { logo } from "../assets";
import { navLinks } from "../constants/Data";
import {
  FaLightbulb,
  FaInfoCircle,
  FaBriefcase,
  FaCode,
  FaGraduationCap,
  FaPhone,
} from "react-icons/fa";

const Navbar: FC = () => {
  const [active, setActive] = useState<string>("home");
  const [toggle, setToggle] = useState<boolean>(false);
  return (
    <nav className=" z-40 w-full bg-primary flex py-6 justify-between items-center navbar">
      <a
        href="#home"
        onClick={() => setToggle(!toggle)}
        className="sm:basis-1/3"
      >
        <div className="flex items-center">
          <img src={logo} alt="hoobank" className="w-[32px] h-[32px]" />
          <div className="logo_name px-2 ">
            <h1 className="font-semibold text-[28px] text-white">
              <span className="username">Herinandrianina</span>
            </h1>
          </div>
        </div>
      </a>

      <ul className="list-none sm:flex hidden justify-end items-center flex-1 ">
        {navLinks.map((nav, index) => (
          <li
            key={index}
            className={`font-poppins font-normal cursor-pointer text-[16px] ${
              active === nav.name ? "text-white" : "text-dimWhite"
            } ${index === navLinks.length - 1 ? "mr-0" : "mr-10"}`}
            onClick={() => setActive(nav.name)}
          >
            <a href={nav.path}>{nav.name}</a>
          </li>
        ))}
      </ul>

      <div className="sm:hidden " style={{ flexBasis: "80%" }}>
        <ul className="list-none flex justify-between items-center ">
          <li>
            <a href="#skills">
              <FaLightbulb style={{ color: "white", fontSize: "23px" }} />
            </a>
          </li>
          <li>
            <a href="#me">
              <FaInfoCircle style={{ color: "white", fontSize: "23px" }} />
            </a>
          </li>
          <li>
            <a href="#projects">
              {" "}
              <FaBriefcase style={{ color: "white", fontSize: "23px" }} />
            </a>
          </li>
          <li>
            <a href="#tech">
              {" "}
              <FaCode style={{ color: "white", fontSize: "23px" }} />
            </a>
          </li>
          <li>
            <a href="#graduates">
              {" "}
              <FaGraduationCap style={{ color: "white", fontSize: "23px" }} />
            </a>
          </li>
          <li>
            <a href="#contact">
              {" "}
              <FaPhone style={{ color: "white", fontSize: "23px" }} />
            </a>
          </li>
        </ul>
      </div>
    </nav>
  );
};

export default Navbar;
