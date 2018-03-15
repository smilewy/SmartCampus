<template>
  <div class="SeatingArrangement">
    <h3>座位布局</h3>
    <el-row style="margin-top: 2rem;height:3.8rem; border-bottom: 1px solid #d2d2d2">
      <el-col :span="24">
        <el-col :span="3">
          <el-col :span="8" style="margin-top:.5rem">
            年级：
          </el-col>
          <el-col :span="16">
            <el-select v-model="grade_id" placeholder="请选择" @change="getClassList(grade_id)">
              <el-option
                v-for="item in grade_list"
                :key="item.gradeId"
                :label="item.name"
                :value="item.gradeId">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
        <el-col :span="3" :offset="1">
          <el-col :span="8" style="margin-top:.5rem">
            班级：
          </el-col>
          <el-col :span="16">
            <el-select v-model="class_id" placeholder="请选择" @change="getClassInfo(class_id)">
              <el-option
                v-for="item in class_list"
                :key="item.classid"
                :label="item.classname"
                :value="item.classid">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
        <el-col :span="3" :offset="1">
          <el-col :span="12" style="margin-top:.5rem">
            座位组数：
          </el-col>
          <el-col :span="12">
            <el-input v-model="group_num" type="number" placeholder="请输入组数"></el-input>
          </el-col>
        </el-col>
        <el-col :span="2" style="margin-left:2rem;border-right:1px solid #d2d2d2;">
          <el-col :span="10">
            <el-button type="primary" @click="createSeat()">生成座位图</el-button>
          </el-col>
        </el-col>
        <el-col :span="3" style="margin: .5rem -1rem 0 2rem;">
          学生总数：<span>{{class_info.number}}</span>
        </el-col>
        <el-col :span="2" style="margin-top:.5rem">
          组数：<span>{{group_num}}</span>
        </el-col>
        <el-col :span="2" style="margin-top:.5rem">
          座位数：<span>{{class_info.number}}/{{seats_num}}</span>
        </el-col>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24">
        <el-col :span="22">
          <div class="Seats-top">讲台</div>
        </el-col>
        <el-col :span="2" class="alertsBtn Seats-Btn">
          <el-button type="primary" :loading="save===true" @click="Saveseat()">保存</el-button>
        </el-col>
      </el-col>
      <el-col v-if="hasLayout" :span="24">
        <div class="Seat-wrap">
          <template v-for="(group,key) in layout">
            <div class="Seat-content">
              <div>
                <img @click="reduceul(key)" class="Seat-btn-jian"
                     src="../../../../../assets/img/ClassManagement/buttton_jian.png" alt="">
                <img @click="addul(key)" class="Seat-btn-jia"
                     src="../../../../../assets/img/ClassManagement/button_jia.png" alt="">
              </div>
              <div class="Seats-group">
                <ul class="Seats-ul" v-for="col in group">
                  <li class="Seats-li" v-for="seat in col">
                    {{seat > class_info.number ? '' : `座位${seat}`}}
                  </li>
                </ul>
              </div>
            </div>
            <div class="Seats-black">过<br>道</div>
          </template>
        </div>
      </el-col>
      <el-col v-else :span="24">
        <p style="padding: 50px 0;text-align: center;">此班没有座位布局</p>
      </el-col>
    </el-row>
    <el-row class="newDoc-top" style="margin:4rem 0 .8rem 0;" type="flex" align="middle">
      <el-col :span="24" class="Car-footer">
        <el-col>1.输入组数，点击生成座位布局表；</el-col>
        <el-col>2.系统会默认每组生成两列，根据学生数自动生成排数，并预留一排座位作为备用；</el-col>
        <el-col>3.系统自动按顺序生成作为优先级；您可以根据需要调整座位优先级，数字越小，优先级越高，例如：座位 <br/>
          优先级是2-1-3，有三名学生成绩为A（498），B（499），C（500），此时如果按成绩生成座位，则实际生成结果顺序 <br/>为B,C,A。
        </el-col>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'

  export default {
    data() {
      return {
        save:false,
        group_num: 4,
        grade_list: [],
        class_list: [],
        grade_id: '',
        class_id: '',
        class_info: {
          number: 0
        },
        group_columes: [],
        layout: {},
        layout_data: {},
        hasLayout: true,
        seats_num: 0,
        needInit:false,
        editState:'init'
      }
    },
    created() {
      this.getGradeList();
      let seatData = JSON.parse(sessionStorage.getItem('seatData'));
      if(seatData){
        setTimeout(()=>{
          this.grade_list = seatData.grade_list;
          this.class_list = seatData.class_list;
          this.layout = seatData.layout;
          this.class_info = seatData.class_info;
          this.grade_id = seatData.grade_id;
          this.class_id = seatData.class_id;
          this.seats_num = seatData.seats_num;
          this.group_num = seatData.group_num;
          this.group_columes = seatData.group_columes;
        });
        setTimeout(()=>{
          this.needInit = true;
        },50)
      }
    },
    methods: {
      getGradeList() {
        req.ajaxSend('/school/ClassManage/common', 'post', {func: 'getClass', param: {kind: 'exam'}}, (res) => {
          this.grade_list = res;
        })
      },
      getClassList(grade_id) {
        let cur_option = this.grade_list.filter(val=>val.gradeId===grade_id)[0];
//        let cur_option = this.grade_list[grade_id];
        this.class_list = cur_option.class;
        if(this.needInit){
          this.class_id='';
        }
      },
      getClassInfo(class_id) {
        if(!this.class_id)return;
        this.class_info = this.class_list.filter(val => val.classid === class_id)[0];
        req.ajaxSend('/school/ClassManage/seatLayout', 'post', {view: 'layout', classId: class_id}, (res) => {
          this.class_info.number = res.total;
          let layout = res.layout;
          this.layout = layout;
          if (layout && layout.group1 && layout.group1.colume1 && layout.group1.colume1.length) {
            let group_num = Object.keys(layout).length,
              rows = layout.group1.colume1.length,
              cols = 0,
              group_columes = [];
            for (let group in layout) {
              let cur_cols = Object.keys(layout[group]).length;
              group_columes.push(cur_cols);
              cols += cur_cols;
            }
            this.seats_num = cols * rows;
            this.group_num = group_num;
            this.hasLayout = true;
            setTimeout(()=>{
              this.group_columes = group_columes;
            })
            sessionStorage.setItem('seatData',JSON.stringify({
              grade_list:this.grade_list,
              class_list:this.class_list,
              layout:this.layout,
              class_info:this.class_info,
              grade_id:this.grade_id,
              class_id:this.class_id,
              seats_num:this.seats_num,
              group_num:this.group_num,
              group_columes:this.group_columes,
            }))
          } else {
            this.seats_num = 0;
            this.group_num = 4;
            this.group_columes = [];
            this.layout = {};
            this.hasLayout = false;
          }
        })
      },
      createSeat() {
        if (!this.grade_id) {
          this.vmMsgWarning('请选择年级');
          return;
        }
        if (!this.class_id) {
          this.vmMsgWarning('请选择班级');
          return;
        }
        if (parseInt(this.group_num) <= 0) {
          this.vmMsgWarning('组数必须大于0');
          return;
        }
        let layout = {},
          group_columes = this.group_columes,
          cols = group_columes.length ? group_columes.reduce((pre, cur) => pre + cur, 0) : this.group_num * 2,
          seats = [],
          rows = Math.ceil(this.class_info.number / cols) + 1,
          toRight = true;

        for (let i = 0, order = 1; i < rows; i++) {
          toRight = !(i%2);
          if(toRight){
            for (let j = 0; j < cols; j++) {
              seats.push(order++);
            }
          }else{
            for (let j = 0,max = order + cols; j < cols; j++) {
              seats.push(--max);
              order++;
            }
          }
        }
        this.seats_num = seats.length;

        let isInit = !group_columes.length;
        for (let group = 1; group <= this.group_num; group++) {
          if (isInit)
            group_columes.push(2);
          layout[`group${group}`] = {};
          for (let i = 0; i < group_columes[group - 1]; i++) {
            layout[`group${group}`][`colume${i + 1}`] = [];
          }
        }

        for (let i = 0; i < rows; i++) {
          for (let j = 0; j <= this.group_num; j++) {
            for (let k = 0; k < group_columes[j]; k++) {
              layout[`group${j + 1}`][`colume${k + 1}`].push(seats.shift());
            }
          }
        }
        this.layout = layout;
        this.hasLayout = true;
        this.editState = 'edited';
        sessionStorage.setItem('seatData',JSON.stringify({
          grade_list:this.grade_list,
          class_list:this.class_list,
          layout:this.layout,
          class_info:this.class_info,
          grade_id:this.grade_id,
          class_id:this.class_id,
          seats_num:this.seats_num,
          group_num:this.group_num,
          group_columes:this.group_columes,
        }))
      },
      reduceul(key) {
        let index = key.replace(/group(.*)/, '$1') - 1;
        if (this.group_columes[index] === 1) {
          this.vmMsgWarning('请至少保留一列');
          return;
        }
        this.group_columes[index]--;
        this.createSeat();
      },
      addul(key) {
        let index = key.replace(/group(.*)/, '$1') - 1;
        this.group_columes[index]++;
        this.createSeat();
      },
      Saveseat() {
        if (!this.hasLayout) {
          this.vmMsgWarning('请先生成座位布局');
          return;
        }
        this.$confirm('保存该座位布局会清空座位安排，是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          req.ajaxSend('/school/ClassManage/seatLayout', 'post', {
            type: 'layoutSave',
            classId: this.class_id,
            class: this.class_info.classname,
            grade: this.grade_id,
            userId: this.class_info.userid,
            layout: this.layout
          }, (res) => {
            this.save=false;
            this.editState = 'saved';
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else{
              this.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
          this.editState = 'init';

        });
      },
    },
    watch: {
      group_num(val) {
        this.group_columes = [];
      }
    },
    beforeRouteLeave (to, from, next) {
      if(this.editState !== 'edited'){
        next();
      }else{
        this.Saveseat()
      }
    }
  }

  function deepCopy(obj) {
    return JSON.parse(JSON.stringify(obj));
  }
</script>
<style lang="less" scoped>
  .SeatingArrangement {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .Seats-top {
    width: 37rem;
    height: 2.5rem;
    background: #d2d2d2;
    margin: 2.25rem auto 0 auto;
    border-radius: .5rem;
    line-height: 2.5rem;
    text-align: center;
    font-size: 1rem;
  }

  .Seats-group {
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    /*margin-left: 1.5rem;*/
  }

  .Seats-ul {
    display: inline-block;
    margin-left: 1.5rem
  }

  .Seats-ul:first-child {
    margin-left: 0;
  }

  .Seats-li {
    width: 6.875rem;
    height: 2.5rem;
    border: 1px solid #d2d2d2;
    border-radius: .3rem;
    text-align: center;
    line-height: 2.5rem;
    margin-top: 1rem;
    cursor: pointer;
  }

  .Seats-li:first-child {
    margin-top: 2rem;
  }

  .Seats-black {
    text-align: center;
    font-size: 1.5rem;
    color: #999999;
    display: inline-block;
    margin: 0 3rem;
    flex-grow: 0;
    flex-shrink: 0;
  }

  .Seats-black:last-of-type {
    display: none;
  }

  @media screen and (max-width: 1280px) {
    .Seats-black {
      width: 7.3rem;
    }
  }

  .Seat-wrap {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    overflow-x: auto;
  }

  .Seat-content {
    min-width: 9rem;
    display: inline-block;
    text-align: center;
    margin: 3.5rem auto 0 auto;
    flex-grow: 0;
    flex-shrink: 0;
  }

  .Seat-btn-jian, .Seat-btn-jia {
    cursor: pointer;
  }

  .Seat-btn-jia {
    margin-left: 1.5rem;
  }

  .Car-footer {
    font-size: 14/16rem;
    color: #999999;
    letter-spacing: .1rem;
  }
</style>
